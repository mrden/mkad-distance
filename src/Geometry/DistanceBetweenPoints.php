<?php

namespace Mrden\MkadDistance\Geometry;

class DistanceBetweenPoints
{
    /**
     * @var Point
     */
    private $from;
    /**
     * @var Point
     */
    private $to;
    /**
     * Расстояние
     * @var float
     */
    private $distance;
    /**
     * DistanceBetweenPoints constructor.
     * @param Point $from
     * @param Point $to
     * @param float $distance
     */
    public function __construct(Point $from, Point $to, float $distance)
    {
        $this->from = $from;
        $this->to = $to;
        $this->distance = $distance;
    }

    /**
     * @return float
     */
    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * @return Point
     */
    public function getFrom(): Point
    {
        return $this->from;
    }

    /**
     * @return Point
     */
    public function getTo(): Point
    {
        return $this->to;
    }
}
