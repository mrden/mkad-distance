<?php

namespace Tests\Factory;

use InvalidArgumentException;
use Mrden\MkadDistance\Distance;
use Mrden\MkadDistance\Geometry\Point;
use Tests\TestCase;

class SpbKadCalculateDistanceTest extends TestCase
{
    public function testCalculatePoint()
    {
        $distance = Distance::calculateByRouteSpbKad(
            new Point(60.021319, 30.654084)
        );
        $this->assertEqualsWithDelta(17, \round($distance), 1);
    }

    public function testCalculateArray()
    {
        $distance = Distance::calculateByRouteSpbKad(
            [60.021319, 30.654084]
        );
        $this->assertEqualsWithDelta(17, \round($distance), 1);
    }

    public function testCalculateAddress()
    {
        $distance = Distance::calculateByRouteSpbKad(
            'Всеволожск, Ленинградская область',
            [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        );
        $this->assertEqualsWithDelta(17, \round($distance), 1);
    }

    public function testCalculatePointByLine()
    {
        $distance = Distance::calculateByLineSpbKad(
            new Point(60.021319, 30.654084)
        );
        $this->assertEqualsWithDelta(10, \round($distance), 1);
    }

    public function testCalculateArrayByLine()
    {
        $distance = Distance::calculateByLineSpbKad(
            [60.021319, 30.654084]
        );
        $this->assertEqualsWithDelta(10, \round($distance), 1);
    }

    public function testCalculateAddressByLine()
    {
        $distance = Distance::calculateByLineSpbKad(
            'Всеволожск, Ленинградская область',
            [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        );
        $this->assertEqualsWithDelta(10, \round($distance), 1);
    }

    public function testFailCalculate()
    {
        $this->expectException(InvalidArgumentException::class);
        Distance::calculateByRouteSpbKad(456);
    }
}
