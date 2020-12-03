<?php
/**
 * Created by PhpStorm.
 * User: Denis Kiselev
 * Date: 10.06.2020
 * Time: 19:24
 */

namespace Mrden\MkadDistance;

use Desarrolla2\Cache\File;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Mrden\MkadDistance\Cache\DistancePredis;
use Mrden\MkadDistance\Exceptions\DistanceExceptions;
use Mrden\MkadDistance\Exceptions\DistanceRequestException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon;
use Psr\SimpleCache\InvalidArgumentException;
use Yandex\Geo\Api;
use Yandex\Geo\Exception;

class Distance
{
    /**
     * Москва МКАД
     */
    const TYPE_MSC_MKAD = 1;

    /**
     * Питер КАД
     */
    const TYPE_SPB_KAD = 2;

    /**
     * API-ключ Яндекс.Карт, получить можно тут https://developer.tech.yandex.ru/services/
     * @var string
     */
    private $yandexGeoCoderApiKey = '';

    /**
     * @var File|DistancePredis
     */
    private $cache;

    /**
     * Время хранения кэша.
     * По-умолчанию 5 дней
     * @var float|int
     */
    private $cacheTtl = 5 * 24 * 60 * 60;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * Distance constructor.
     * @param string $yandexGeoCoderApiKey
     * @param \Predis\Client|null $predisClient
     * @param string $cachePrefix
     */
    public function __construct(string $yandexGeoCoderApiKey = '', \Predis\Client $predisClient = null, $cachePrefix = '')
    {
        $this->yandexGeoCoderApiKey = $yandexGeoCoderApiKey;

        // Кэширование в redis или в файлах
        if ($predisClient) {
            $this->cache = new DistancePredis($predisClient);
        } else {
            $cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cacheForMkad';
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir);
            }
            $this->cache = new File($cacheDir);
        }
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * Рассчитать расстояние за МКАД в метрах
     * @param array|Point|string $param
     * @param int $type
     * @return null|float
     * @throws DistanceExceptions
     * @throws InvalidArgumentException
     */
    public function calculate($param, int $type = self::TYPE_MSC_MKAD)
    {
        $target = $this->createPoint($param);
        $polygon = $this->createPolygon($type);

        // Определение внутри МКАД или снаружи
        $isInner = $polygon->isInner($target);

        if ($isInner) {
            throw new Exceptions\InnerMkadException('Target point located inside the current KAD.');
        }

        $polygonJunctions = $this->createJunctionsPolygon($type);
        $neededMkadCoordinates = [];
        // Процесс отсечения самых дальних точек развязок на МКАД
        foreach ($polygonJunctions->getVertices() as $vertex) {
            $lineDistance = $vertex->distanceToPoint($target);
            $neededMkadCoordinates[$lineDistance] = $vertex;
        }
        ksort($neededMkadCoordinates);
        // Расстояние по прямой от целевой точки до самой ближайшей развязки на МКАД
        $mkadLineDistance = array_key_first($neededMkadCoordinates);
        $client = new Client();
        $current = 0;
        // Учитываем $limit ближайших (по расстоянию по прямой) развязок для расчета расстояний от них
        $limit = 6;
        $mkadPintsDistances = [];

        foreach ($neededMkadCoordinates as $lineDistance => $mkadJunctionsPoint) {
            $url = sprintf(
                "https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s?overview=false",
                $mkadJunctionsPoint->getLon(),
                $mkadJunctionsPoint->getLat(),
                $target->getLon(),
                $target->getLat()
            );
            $cacheKey = $this->cachePrefix . '.osrm.' . md5($url);
            if ($this->cache instanceof DistancePredis) {
                $cacheKey = $this->cachePrefix . ':osrm:' . md5($url);
            }
            if ($this->cache->has($cacheKey)) {
                $resJson = $this->cache->get($cacheKey);
            } else {
                try {
                    $response = $client->get($url);
                } catch (RequestException $e) {
                    throw new DistanceRequestException($e->getMessage(), $e->getCode(), $e, $mkadLineDistance);
                }
                $resJson = json_decode((string)$response->getBody(), true);
                $this->cache->set($cacheKey, $resJson, $this->cacheTtl);
            }
            $mkadPintsDistances[$lineDistance] = $resJson['routes'][0]['distance'];
            $current++;

            if ($current >= $limit) {
                break;
            }
        }

        return (float)min($mkadPintsDistances);
    }

    /**
     * @param $type
     * @return Polygon
     * @throws DistanceExceptions
     */
    private function createJunctionsPolygon($type)
    {
        switch ($type) {
            case self::TYPE_MSC_MKAD:
                return new Polygon\MscMkadJunctions();
            case self::TYPE_SPB_KAD:
                return new Polygon\SpbKadJunctions();
            default:
                throw new DistanceExceptions('Not detected junctions coordinates current KAD type.');
        }
    }

    /**
     * @param int $type
     * @return Polygon
     * @throws DistanceExceptions
     */
    private function createPolygon(int $type)
    {
        switch ($type) {
            case self::TYPE_MSC_MKAD:
                return new Polygon\MscMkad();
            case self::TYPE_SPB_KAD:
                return new Polygon\SpbKad();
            default:
                throw new DistanceExceptions('Not detected coordinates current KAD type.');
        }
    }

    /**
     * @param Point|float[]|string $param
     * @return Point
     * @throws DistanceExceptions
     * @throws InvalidArgumentException
     */
    private function createPoint($param)
    {
        $target = null;
        if ($param instanceof Point) {
            $target = $param;
        } elseif (is_array($param)) {
            $target = Point::createFromArray($param);
        } elseif (is_string($param) && $this->yandexGeoCoderApiKey) {
            $cacheKey = 'geocoder.' . md5(strtolower($param));
            if ($this->cache instanceof DistancePredis) {
                $cacheKey = 'geocoder:' . md5(strtolower($param));
            }
            if ($this->cache->has($cacheKey)) {
                $response = $this->cache->get($cacheKey);
            } else {
                $api = new Api();
                $api->setToken($this->yandexGeoCoderApiKey);
                try {
                    $response = $api->setToken($this->yandexGeoCoderApiKey)
                        ->setQuery($param)
                        ->setLimit(1)
                        ->load()
                        ->getResponse();

                    $this->cache->set($cacheKey, $response, $this->cacheTtl);
                } catch (Exception $e) {
                    throw new DistanceExceptions($e->getMessage(), $e->getCode(), $e);
                }
            }

            if ($response && $response->getList()) {
                $target = Point::createFromArray([
                    $response->getList()[0]->getData()['Latitude'],
                    $response->getList()[0]->getData()['Longitude'],
                ]);
            } else {
                throw new DistanceExceptions('No result from GeoCoder.');
            }
        }

        if ($target === null) {
            throw new DistanceExceptions('Target point not detected.');
        }

        return $target;
    }

    /**
     * @param string $yandexGeoCoderApiKey
     */
    public function setYandexGeoCoderApiKey(string $yandexGeoCoderApiKey): void
    {
        $this->yandexGeoCoderApiKey = $yandexGeoCoderApiKey;
    }
}
