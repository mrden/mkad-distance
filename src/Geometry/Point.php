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

    public function __construct(float $lat, float $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLon(): float
    {
        return $this->lon;
    }

    public function __toString(): string
    {
        return \sprintf('%s,%s', $this->lon, $this->lat);
    }

    public static function compare(Point $p1, Point $p2): bool
    {
        return $p1->getLat() == $p2->getLat() && $p1->getLon() == $p2->getLon();
    }

    /**
     * @param array{0: float, 1: float} $coordinate
     * @return Point
     */
    public static function createFromArray(array $coordinate): Point
    {
        return new Point($coordinate[0], $coordinate[1]);
    }
}
