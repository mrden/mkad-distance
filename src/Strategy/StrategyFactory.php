<?php

namespace Mrden\MkadDistance\Strategy;

use InvalidArgumentException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon;
use Mrden\MkadDistance\Iterface\DistanceCalculatorStrategy;

class StrategyFactory
{
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param $target
     * @param Polygon $basePolygon
     * @param Polygon $junctionsPolygon
     * @return DistanceCalculatorStrategy
     * @throws InvalidArgumentException
     */
    public function create(
        $target, Polygon $basePolygon,
        Polygon $junctionsPolygon
    ): ?DistanceCalculatorStrategy
    {
        $cache = $this->options['cache'] ?? null;

        if ($target instanceof Point) {
            return new PointDistanceCalculator($basePolygon, $junctionsPolygon, $cache);
        }

        if (is_array($target)) {
            return new ArrayDistanceCalculator($basePolygon, $junctionsPolygon, $cache);
        }

        if (is_string($target) && isset($this->options['yandexGeoCoderApiKey'])) {
            return new AddressDistanceCalculator(
                (string)$this->options['yandexGeoCoderApiKey'],
                $basePolygon,
                $junctionsPolygon,
                $cache
            );
        }

        throw new InvalidArgumentException('Target param incorrect type');
    }
}