<?php

namespace Tests\Factory;

use InvalidArgumentException;
use Mrden\MkadDistance\Distance;
use Mrden\MkadDistance\Geometry\Point;
use Tests\TestCase;

class MscMkadCalculateDistanceTest extends TestCase
{
    public function testCalculatePoint()
    {
        $distance = Distance::calculateByRouteToMoscowMkad(
            new Point(55.729826, 36.854462)
        );
        $this->assertEqualsWithDelta(45, \round($distance), 1);
    }

    public function testCalculateArray()
    {
        $distance = Distance::calculateByRouteToMoscowMkad(
            [55.729826, 36.854462]
        );
        $this->assertEqualsWithDelta(45, \round($distance), 1);
    }

    public function testCalculateAddress()
    {
        $distance = Distance::calculateByRouteToMoscowMkad(
            'Звенигород, Московская область',
            [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        );
        $this->assertEqualsWithDelta(45, \round($distance), 1);
    }

    public function testCalculatePointByLine()
    {
        $distance = Distance::calculateByLineToMoscowMkad(
            new Point(55.729826, 36.854462)
        );
        $this->assertEqualsWithDelta(32, \round($distance), 1);
    }

    public function testCalculateArrayByLine()
    {
        $distance = Distance::calculateByLineToMoscowMkad(
            [55.729826, 36.854462]
        );
        $this->assertEqualsWithDelta(32, \round($distance), 1);
    }

    public function testCalculateAddressByLine()
    {
        $distance = Distance::calculateByLineToMoscowMkad(
            'Звенигород, Московская область',
            [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        );
        $this->assertEqualsWithDelta(32, \round($distance), 1);
    }

    public function testFailCalculate()
    {
        $this->expectException(InvalidArgumentException::class);
        Distance::calculateByRouteToMoscowMkad(456);
    }
}
