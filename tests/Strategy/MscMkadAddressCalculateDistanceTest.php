<?php

namespace Tests\Strategy;

use InvalidArgumentException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon\MscMkad;
use Mrden\MkadDistance\Geometry\Polygon\MscMkadJunctions;
use Mrden\MkadDistance\Contracts\DistanceCalculator;
use Mrden\MkadDistance\Calculator\AddressDistanceCalculator;
use Tests\TestCase;

class MscMkadAddressCalculateDistanceTest extends TestCase
{
    /**
     * @var DistanceCalculator
     */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new AddressDistanceCalculator(
            $this->getYandexGeoCoderApiKey(),
            new MscMkad(),
            new MscMkadJunctions()
        );
    }

    public function testCalculateByRouteToZvenigorod(): void
    {
        $distance = $this->calculator->calculate('Звенигород, Московская область');
        $this->assertEqualsWithDelta(45, \round($distance->getDistance() / 1000), 1);
    }

    public function testCalculateByLineToZvenigorod(): void
    {
        $distance = $this->calculator->calculate('Звенигород, Московская область', false);
        $this->assertEqualsWithDelta(32, \round($distance->getDistance() / 1000), 1);
    }

    public function testFailCalculateByAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate(new Point(55.729826, 36.854462));
    }

    public function testFailCalculateByArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([55.729826, 36.854462]);
    }
}
