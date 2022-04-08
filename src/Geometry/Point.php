<?php

namespace Mrden\MkadDistance\Geometry;

class Point
{
    /**
     * @var float
     */
    private $lat;
    /**
     * @var float
     */
    private $lon;

    /**
     * Point constructor.
     * @param float $lat
     * @param float $lon
     */
    public function __construct(float $lat, float $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }

    /**
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLon(): float
    {
        return $this->lon;
    }

    public function __toString(): string
    {
        return sprintf('%s,%s', $this->lon, $this->lat);
    }

    /**
     * @param Point $p1
     * @param Point $p2
     * @return bool
     */
    public static function compare(Point $p1, Point $p2): bool
    {
        return $p1->getLat() == $p2->getLat() && $p1->getLon() == $p2->getLon();
    }

    /**
     * @param array|float[] $coordinate
     * @return Point
     */
    public static function createFromArray(array $coordinate): Point
    {
        return new Point($coordinate[0], $coordinate[1]);
    }
}