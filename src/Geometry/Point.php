<?php
/**
 * Created by PhpStorm.
 * User: Denis Kiselev
 * Date: 10.06.2020
 * Time: 18:21
 */

namespace Mrden\MkadDistance\Geometry;

class Point
{
    /**
     * Радиус Земли
     * @var float
     */
    const EARTH_RADIUS = 6372795.0;
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

    /**
     * @param Point $point
     * @return float
     */
    public function distanceToPoint(Point $point)
    {
        // перевести координаты в радианы
        $lat1 = $this->lat * M_PI / 180;
        $lat2 = $point->getLat() * M_PI / 180;
        $long1 = $this->lon * M_PI / 180;
        $long2 = $point->getLon() * M_PI / 180;

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
        return $ad * self::EARTH_RADIUS;
    }

    /**
     * @param Point $p1
     * @param Point $p2
     * @return bool
     */
    public static function compare(Point $p1, Point $p2){
        return $p1->getLat() == $p2->getLat() && $p1->getLon() == $p2->getLon();
    }

    /**
     * @param array $coordinate
     * @return Point
     */
    public static function createFromArray(array $coordinate)
    {
        return new Point($coordinate[0], $coordinate[1]);
    }
}