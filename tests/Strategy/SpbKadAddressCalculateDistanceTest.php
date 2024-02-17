<?php

namespace Tests\Strategy;

use InvalidArgumentException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon\SpbKad;
use Mrden\MkadDistance\Geometry\Polygon\SpbKadJunctions;
use Mrden\MkadDistance\Contracts\DistanceCalculator;
use Mrden\MkadDistance\Calculator\AddressDistanceCalculator;
use Tests\TestCase;

class SpbKadAddressCalculateDistanceTest extends TestCase
{
    /**
     * @var DistanceCalculator
     */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AddressDistanceCalculator(
            $this->getYandexGeoCoderApiKey(),
            new SpbKad(),
            new SpbKadJunctions()
        );
    }

    public function testCalculateByRouteToVsevolozhsk(): void
    {
        $distance = $this->calculator->calculate('Всеволожск, Ленинградская область');
        $this->assertEqualsWithDelta(17, \round($distance->getDistance() / 1000), 1);
    }

    public function testCalculateByLineToVsevolozhsk(): void
    {
        $distance = $this->calculator->calculate('Всеволожск, Ленинградская область', false);
        $this->assertEqualsWithDelta(10, \round($distance->getDistance() / 1000), 1);
    }

    public function testFailCalculateByPoint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate(new Point(60.021319, 30.654084));
    }

    public function testFailCalculateByArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([60.021319, 30.654084]);
    }
}
