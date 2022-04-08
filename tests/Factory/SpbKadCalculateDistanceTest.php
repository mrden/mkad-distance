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
        $distance = Distance::createSpbKadCalculator(
            new Point(60.021319, 30.654084)
        )->calculate();
        $this->assertEquals(17.57, $distance);
    }

    public function testCalculateArray()
    {
        $distance = Distance::createSpbKadCalculator(
            [60.021319, 30.654084]
        )->calculate();
        $this->assertEquals(17.57, $distance);
    }

    public function testCalculateAddress()
    {
        $distance = Distance::createSpbKadCalculator(
            'Всеволожск, Ленинградская область', [
                'yandexGeoCoderApiKey' => $this->getYandexGeoCoderApiKey(),
            ]
        )->calculate();
        $this->assertEquals(17.57, $distance);
    }

    public function testFailCalculate()
    {
        $this->expectException(InvalidArgumentException::class);
        Distance::createSpbKadCalculator(456);
    }
}