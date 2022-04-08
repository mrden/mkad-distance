<?php

namespace Mrden\MkadDistance\Iterface;

use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;

interface DistanceCalculatorStrategy
{
    /**
     * @param $target
     * @param bool $calcByRoutes
     * @return DistanceBetweenPoints
     */
    public function calculate($target, bool $calcByRoutes = true): DistanceBetweenPoints;
}