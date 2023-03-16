<?php

namespace Tests\Strategy;

use InvalidArgumentException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon\MscMkad;
use Mrden\MkadDistance\Geometry\Polygon\MscMkadJunctions;
use Mrden\MkadDistance\Iterface\DistanceCalculatorStrategy;
use Mrden\MkadDistance\Strategy\AddressDistanceCalculator;
use Tests\TestCase;

class MscMkadAddressCalculateDistanceTest extends TestCase
{
    /**
     * @var DistanceCalculatorStrategy
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
        $this->assertEquals(45.58, $distance);
    }

    public function testCalculateByLineToZvenigorod(): void
    {
        $distance = $this->calculator->calculate('Звенигород, Московская область', false);
        $this->assertEquals(32.14, $distance);
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
