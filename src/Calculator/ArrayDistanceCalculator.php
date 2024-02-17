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
     * @param array $target
     * @throws DistanceRequestException
     * @throws InnerPolygonException
     * @throws DistanceException
     * @throws \InvalidArgumentException
     */
    public function calculate($target, bool $calcByRoutes = true): DistanceBetweenPoints
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
