<?php

namespace Mrden\MkadDistance\Calculator;

use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon;
use Mrden\MkadDistance\Contracts\DistanceCalculator;

class StrategyCalculatorFactory
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param array{0: float, 1: float}|string|Point $target
     * @throws \InvalidArgumentException
     */
    public function create(
        Point|array|string $target,
        Polygon            $basePolygon,
        Polygon            $junctionsPolygon
    ): ?DistanceCalculator {
        $cache = $this->options['cache'] ?? null;

        if ($target instanceof Point) {
            return new PointDistanceCalculator($basePolygon, $junctionsPolygon, $cache);
        }

        if (\is_array($target)) {
            return new ArrayDistanceCalculator($basePolygon, $junctionsPolygon, $cache);
        }

        if (\is_string($target) && isset($this->options['yandexGeoCoderApiKey'])) {
            return new AddressDistanceCalculator(
                (string)$this->options['yandexGeoCoderApiKey'],
                $basePolygon,
                $junctionsPolygon,
                $cache
            );
        }

        throw new \InvalidArgumentException('Target param incorrect type');
    }
}
