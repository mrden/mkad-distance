<?php

namespace Mrden\MkadDistance\Calculator;

use Mrden\MkadDistance\Exception\DistanceException;
use Mrden\MkadDistance\Exception\InnerPolygonException;
use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as CacheInvalidArgumentException;
use Yandex\Geo\Api;

class AddressDistanceCalculator extends PointDistanceCalculator
{
    private Api $api;

    public function __construct(
        string $yandexGeoCoderApiKey,
        Polygon $basePolygon,
        Polygon $junctionsPolygon,
        ?CacheInterface $cache = null,
        int $cacheTtl = 5 * 24 * 60 * 60
    ) {
        $this->api = new Api();
        $this->api->setToken($yandexGeoCoderApiKey);
        parent::__construct($basePolygon, $junctionsPolygon, $cache, $cacheTtl);
    }

    /**
     * @throws DistanceException
     * @throws InnerPolygonException
     * @throws \InvalidArgumentException
     * @throws CacheInvalidArgumentException
     */
    public function calculate(Point|array|string $target, bool $calcByRoutes = true): DistanceBetweenPoints
    {
        if (!\is_string($target)) {
            throw new \InvalidArgumentException('Target param most be string address');
        }
        $cacheKey = 'geocoder.' . \md5(\strtolower($target));
        try {
            if ($this->cache && $this->cache->has($cacheKey)) {
                $response = $this->cache->get($cacheKey);
            } else {
                $response = $this->api->setQuery($target)
                    ->setLimit(1)
                    ->load()
                    ->getResponse();

                $this->cache?->set($cacheKey, $response, $this->cacheTtl);
            }
        } catch (\Exception $e) {
            throw new DistanceException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response && $response->getList()) {
            $target = Point::createFromArray([
                $response->getList()[0]->getData()['Latitude'],
                $response->getList()[0]->getData()['Longitude'],
            ]);
        } else {
            throw new DistanceException('No result from Yandex GeoCoder.');
        }
        return parent::calculate($target, $calcByRoutes);
    }
}
