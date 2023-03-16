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
        $distance = Distance::createMoscowMkadCalculator(
            new Point(55.729826, 36.854462)
        )->calculate();
        $this->assertEquals(45.58, $distance);
    }

    public function testCalculateArray()
    {
        $distance = Distance::createMoscowMkadCalculator(
            [55.729826, 36.854462]
        )->calculate();
        $this->assertEquals(45.58, $distance);
    }

    public function testCalculateAddress()
    {
        $distance = Distance::createMoscowMkadCalculator(
            'Звенигород, Московская область', [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        )->calculate();
        $this->assertEquals(45.58, $distance);
    }

    public function testFailCalculate()
    {
        $this->expectException(InvalidArgumentException::class);
        Distance::createMoscowMkadCalculator(456);
    }
}
