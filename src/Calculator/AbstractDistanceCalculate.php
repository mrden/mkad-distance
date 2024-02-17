<?php

namespace Mrden\MkadDistance\Calculator;

use Mrden\MkadDistance\Exception\DistanceException;
use Mrden\MkadDistance\Exception\DistanceRequestException;
use Mrden\MkadDistance\Exception\RouteNotFoundException;
use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon;
use OSRM\Service\RouteService;
use Psr\SimpleCache\InvalidArgumentException as CacheInvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractDistanceCalculate
{
    /**
     * Радиус Земли
     * @var float
     */
    private const EARTH_RADIUS = 6372795.0;

    /**
     * @var Polygon
     */
    protected $basePolygon;
    /**
     * @var Polygon
     */
    protected $junctionsPolygon;

    /**
     * @var CacheInterface|null
     */
    protected $cache;

    /**
     * Default 5 days
     * @var int
     */
    protected $cacheTtl;

    public function __construct(
        Polygon $basePolygon,
        Polygon $junctionsPolygon,
        ?CacheInterface $cache = null,
        int $cacheTtl = 5 * 24 * 60 * 60
    ) {
        $this->basePolygon = $basePolygon;
        $this->junctionsPolygon = $junctionsPolygon;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    protected function calculateLineDistance(Point $from, Point $to): DistanceBetweenPoints
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
     * @throws DistanceRequestException
     * @throws CacheInvalidArgumentException
     */
    protected function calculateRouteDistance(Point $from, Point $to): DistanceBetweenPoints
    {
        $route = new RouteService();
        $route->setOverview('false');
        $coordinates = sprintf('%s;%s', $from, $to);
        $cacheKey = self::class . '.osrm.' . md5($route->getUri());
        if ($this->cache && $this->cache->has($cacheKey)) {
            $result = $this->cache->get($cacheKey);
        } else {
            try {
                $response = $route->fetch($coordinates);
                if ($response->isOK()) {
                    $result = $response->toArray();
                    if (empty($result['routes'])) {
                        throw new DistanceException('Bad response.');
                    }
                    if ($this->cache) {
                        $this->cache->set($cacheKey, $result, $this->cacheTtl);
                    }
                } else {
                    throw new RouteNotFoundException('Route not found.');
                }
            } catch (\Exception $e) {
                throw new DistanceRequestException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                    $this->calculateLineDistance($from, $to)
                );
            }
        }
        return new DistanceBetweenPoints($from, $to, (float)($result['routes'][0]['distance'] ?? 0));
    }

    /**
     * @param list<DistanceBetweenPoints> $distances
     */
    protected function findMinDistance(array $distances): ?DistanceBetweenPoints
    {
        if (empty($distances)) {
            return null;
        }
        $invalidDistances = \array_filter($distances, function ($distance) {
            return !$distance instanceof DistanceBetweenPoints;
        });
        if (!empty($invalidDistances)) {
            throw new \InvalidArgumentException(
                \sprintf('Element from array must be %s type', DistanceBetweenPoints::class)
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
}
