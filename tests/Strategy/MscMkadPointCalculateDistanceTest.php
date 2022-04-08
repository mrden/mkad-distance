<?php

namespace Tests\Strategy;

use InvalidArgumentException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon\MscMkad;
use Mrden\MkadDistance\Geometry\Polygon\MscMkadJunctions;
use Mrden\MkadDistance\Iterface\DistanceCalculatorStrategy;
use Mrden\MkadDistance\Strategy\PointDistanceCalculator;
use Tests\TestCase;

class MscMkadPointCalculateDistanceTest extends TestCase
{
    /**
     * @var DistanceCalculatorStrategy
     */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointDistanceCalculator(
            new MscMkad(),
            new MscMkadJunctions()
        );
    }

    public function testCalculateByRouteToZvenigorod(): void
    {
        $distance = $this->calculator->calculate(new Point(55.729826, 36.854462));
        $this->assertEquals(45.58, $distance);
    }

    public function testCalculateByLineToZvenigorod(): void
    {
        $distance = $this->calculator->calculate(new Point(55.729826, 36.854462), false);
        $this->assertEquals(32.14, $distance);
    }

    public function testFailCalculateByAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate('Звенигород, Московская область');
    }

    public function testFailCalculateByArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([55.729826, 36.854462]);
    }
}