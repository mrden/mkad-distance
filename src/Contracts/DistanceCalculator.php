<?php

namespace Mrden\MkadDistance\Contracts;

use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;

interface DistanceCalculator
{
    /**
     * @param Point|array{0: float, 1: float}|string $target
     */
    public function calculate(Point|array|string $target, bool $calcByRoutes = true): DistanceBetweenPoints;
}
