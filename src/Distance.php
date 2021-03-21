<?php
/**
 * Created by PhpStorm.
 * User: Denis Kiselev
 * Date: 10.06.2020
 * Time: 19:24
 */

namespace Mrden\MkadDistance;

use Desarrolla2\Cache\AbstractCache;
use Desarrolla2\Cache\File;
use Exception;
use Mrden\MkadDistance\Cache\DistancePredis;
use Mrden\MkadDistance\Exceptions\DistanceException;
use Mrden\MkadDistance\Exceptions\DistanceRequestException;
use Mrden\MkadDistance\Exceptions\RouteNotFoundException;
use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon;
use Mrden\MkadDistance\Route\Route;
use Predis\Client;
use Psr\SimpleCache\InvalidArgumentException;
use Yandex\Geo\Api;
use Yandex\Geo\Exception as YandexGeoException;

class Distance
{
    /**
     * Радиус Земли
     * @var float
     */
    public const EARTH_RADIUS = 6372795.0;
    /**
     * Москва МКАД
     */
    public const TYPE_MSC_MKAD = 1;

    /**
     * Питер КАД
     */
    public const TYPE_SPB_KAD = 2;

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
     * @param Client|null $predisClient
     * @param string $cachePrefix
     */
    public function __construct(string $yandexGeoCoderApiKey = '', Client $predisClient = null, $cachePrefix = '')
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
     * Расстояние по прямой между двумя точками
     * @param Point $from
     * @param Point $to
     * @return DistanceBetweenPoints
     */
    public static function calculateLineDistance(Point $from, Point $to): DistanceBetweenPoints
    {
        // перевести координаты в радианы
        $lat1 = $from->getLat() * M_PI / 180;
        $lat2 = $to->getLat() * M_PI / 180;
        $long1 = $from->getLon() * M_PI / 180;
        $long2 = $to->getLon() * M_PI / 180;
        // косинусы и синусы широт и разницы долгот
        $cl1 = cos($lat1);
        $cl2 = cos($lat2);
        $sl1 = sin($lat1);
        $sl2 = sin($lat2);
        $delta = $long2 - $long1;
        $cdelta = cos($delta);
        $sdelta = sin($delta);
        // вычисления длины большого круга
        $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;
        $ad = atan2($y, $x);
        return new DistanceBetweenPoints($from, $to, $ad * self::EARTH_RADIUS);
    }

    /**
     * @param Point $from
     * @param Point $to
     * @param AbstractCache $cache
     * @param string $cachePrefix
     * @param int $cacheTtl
     * @return DistanceBetweenPoints
     * @throws DistanceRequestException
     * @throws InvalidArgumentException
     */
    public static function calculateRouteDistance(
        Point $from,
        Point $to,
        AbstractCache $cache,
        string $cachePrefix = '',
        int $cacheTtl = 5 * 24 * 60 * 60
    ): DistanceBetweenPoints {
        $route = new Route();
        $route->setOverview('false');
        $coordinates = sprintf('%s;%s', $from, $to);
        $route->setCoordinates($coordinates);
        $cacheKey = $cachePrefix . '.osrm.' . md5($route->getUri());
        if ($cache instanceof DistancePredis) {
            $cacheKey = str_replace('.osrm.', ':osrm:', $cacheKey);
        }
        if ($cache->has($cacheKey)) {
            $result = $cache->get($cacheKey);
        } else {
            try {
                $response = $route->fetch($coordinates);
                if ($response->isOK()) {
                    $result = $response->toArray();
                    if (!isset($result['routes']) || empty($result['routes'])) {
                        throw new DistanceException('Bad response.');
                    }
                    $cache->set($cacheKey, $result, $cacheTtl);
                } else {
                    throw new RouteNotFoundException('Route not found.');
                }
            } catch (Exception $e) {
                throw new DistanceRequestException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                    self::calculateLineDistance($from, $to)
                );
            }
        }
        return new DistanceBetweenPoints($from, $to, (float)$result['routes'][0]['distance']);
    }

    /**
     * @param DistanceBetweenPoints[] $distances
     * @return DistanceBetweenPoints|null
     */
    private function findMinDistance(array $distances): ?DistanceBetweenPoints
    {
        if (empty($distances)) {
            return null;
        }
        if (!$distances[array_key_first($distances)] instanceof DistanceBetweenPoints) {
            throw new \InvalidArgumentException(
                sprintf('Element from array most be %s type', DistanceBetweenPoints::class)
            );
        }
        $min = null;
        foreach ($distances as $distance) {
            if ($min === null || $min->getDistance() > $distance->getDistance()) {
                $min = $distance;
            }
        }
        return $min;
    }

    /**
     * Рассчитать расстояние за МКАД в метрах
     * @param array|Point|string $param
     * @param int $type
     * @param bool $calcRouteDistance
     * @return DistanceBetweenPoints
     * @throws DistanceException
     * @throws DistanceRequestException
     * @throws Exceptions\InnerMkadException
     * @throws InvalidArgumentException
     */
    public function calculate($param, int $type = self::TYPE_MSC_MKAD, bool $calcRouteDistance = true): DistanceBetweenPoints
    {
        $target = $this->createPoint($param);
        $polygon = $this->createPolygon($type);

        // Определение внутри МКАД или снаружи
        $isInner = $polygon->isInner($target);

        if ($isInner) {
            throw new Exceptions\InnerMkadException('Target point located inside the current KAD.');
        }

        $polygonJunctions = $this->createJunctionsPolygon($type);
        $mkadLineDistances = [];
        // Процесс отсечения самых дальних точек развязок на МКАД
        foreach ($polygonJunctions->getVertices() as $vertex) {
            $mkadLineDistances[] = self::calculateLineDistance($vertex, $target);
        }
        // Расстояние по прямой от целевой точки до самой ближайшей развязки на МКАД
        $minMkadLineDistance = $this->findMinDistance($mkadLineDistances);
        $current = 0;
        // Учитываем $limit ближайших (по расстоянию по прямой) развязок для расчета расстояний от них
        $limit = 6;
        $mkadRouteDistances = [];

        if (!$calcRouteDistance) {
            return $minMkadLineDistance;
        }

        foreach ($mkadLineDistances as $lineDistance => $mkadLineDistance) {
            try {
                $mkadRouteDistances[] = self::calculateRouteDistance(
                    $mkadLineDistance->getFrom(),
                    $mkadLineDistance->getTo(),
                    $this->cache,
                    $this->cachePrefix,
                    $this->cacheTtl
                );
            } catch (Exception $e) {
                throw new DistanceRequestException($e->getMessage(), $e->getCode(), $e, $minMkadLineDistance);
            }
            $current++;
            if ($current >= $limit) {
                break;
            }
        }

        return $this->findMinDistance($mkadRouteDistances);
    }

    /**
     * @param $type
     * @return Polygon
     * @throws DistanceException
     */
    private function createJunctionsPolygon($type): Polygon
    {
        switch ($type) {
            case self::TYPE_MSC_MKAD:
                return new Polygon\MscMkadJunctions();
            case self::TYPE_SPB_KAD:
                return new Polygon\SpbKadJunctions();
            default:
                throw new DistanceException('Not detected junctions coordinates current KAD type.');
        }
    }

    /**
     * @param int $type
     * @return Polygon
     * @throws DistanceException
     */
    private function createPolygon(int $type): Polygon
    {
        switch ($type) {
            case self::TYPE_MSC_MKAD:
                return new Polygon\MscMkad();
            case self::TYPE_SPB_KAD:
                return new Polygon\SpbKad();
            default:
                throw new DistanceException('Not detected coordinates current KAD type.');
        }
    }

    /**
     * @param Point|float[]|string $param
     * @return Point
     * @throws DistanceException
     * @throws InvalidArgumentException
     */
    private function createPoint($param): Point
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
                } catch (YandexGeoException $e) {
                    throw new DistanceException($e->getMessage(), $e->getCode(), $e);
                }
            }

            if ($response && $response->getList()) {
                $target = Point::createFromArray([
                    $response->getList()[0]->getData()['Latitude'],
                    $response->getList()[0]->getData()['Longitude'],
                ]);
            } else {
                throw new DistanceException('No result from GeoCoder.');
            }
        }

        if ($target === null) {
            throw new DistanceException('Target point not detected.');
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
