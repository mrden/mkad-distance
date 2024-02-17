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
        $distance = Distance::calculateByRouteSpbKadCalculator(
            new Point(60.021319, 30.654084)
        );
        $this->assertEqualsWithDelta(17, \round($distance), 1);
    }

    public function testCalculateArray()
    {
        $distance = Distance::calculateByRouteSpbKadCalculator(
            [60.021319, 30.654084]
        );
        $this->assertEqualsWithDelta(17, \round($distance), 1);
    }

    public function testCalculateAddress()
    {
        $distance = Distance::calculateByRouteSpbKadCalculator(
            'Всеволожск, Ленинградская область',
            [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        );
        $this->assertEqualsWithDelta(17, \round($distance), 1);
    }

    public function testCalculatePointByLine()
    {
        $distance = Distance::calculateByLineSpbKadCalculator(
            new Point(60.021319, 30.654084)
        );
        $this->assertEqualsWithDelta(10, \round($distance), 1);
    }

    public function testCalculateArrayByLine()
    {
        $distance = Distance::calculateByLineSpbKadCalculator(
            [60.021319, 30.654084]
        );
        $this->assertEqualsWithDelta(10, \round($distance), 1);
    }

    public function testCalculateAddressByLine()
    {
        $distance = Distance::calculateByLineSpbKadCalculator(
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
        Distance::calculateByRouteSpbKadCalculator(456);
    }
}
