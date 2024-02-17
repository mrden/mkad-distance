<?php

namespace Mrden\MkadDistance\Calculator;

use Mrden\MkadDistance\Exception\DistanceException;
use Mrden\MkadDistance\Exception\DistanceRequestException;
use Mrden\MkadDistance\Exception\InnerPolygonException;
use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;

class ArrayDistanceCalculator extends PointDistanceCalculator
{
    /**
     * @param Point|array{0: float, 1: float}|string $target
     * @throws DistanceRequestException
     * @throws InnerPolygonException
     * @throws DistanceException
     * @throws \InvalidArgumentException
     */
    public function calculate(Point|array|string $target, bool $calcByRoutes = true): DistanceBetweenPoints
    {
        if (!\is_array($target)) {
            throw new \InvalidArgumentException(
                'Target param most be array coordinates [float $lat, float $lon]'
            );
        }
        $target = Point::createFromArray($target);
        return parent::calculate($target, $calcByRoutes);
    }
}
