<?php

namespace Mrden\MkadDistance;

use InvalidArgumentException;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon\MscMkad;
use Mrden\MkadDistance\Geometry\Polygon\MscMkadJunctions;
use Mrden\MkadDistance\Geometry\Polygon\SpbKad;
use Mrden\MkadDistance\Geometry\Polygon\SpbKadJunctions;
use Mrden\MkadDistance\Iterface\DistanceCalculatorStrategy;
use Mrden\MkadDistance\Strategy\StrategyFactory;

class Distance
{
    /**
     * @var DistanceCalculatorStrategy
     */
    private $calculator;

    /**
     * @var string|array|Point
     */
    private $target;

    public function __construct(DistanceCalculatorStrategy $calculator, $target = null)
    {
        $this->calculator = $calculator;
        $this->target = $target;
    }

    /**
     * Distance in kilometers
     * @param bool $calByRoutes
     * @return float
     */
    public function calculate(bool $calByRoutes = true): float
    {
        return round($this->calculator->calculate($this->target, $calByRoutes)->getDistance() / 1000, 2);
    }

    /**
     * @param $target
     * @param array $options
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createMoscowMkadCalculator($target, array $options = []): Distance
    {
        $strategyFactory = new StrategyFactory($options);
        return new Distance(
            $strategyFactory->create($target, new MscMkad(), new MscMkadJunctions()),
            $target
        );
    }

    /**
     * @param $target
     * @param array $options
     * @return Distance
     * @throws InvalidArgumentException
     */
    public static function createSpbKadCalculator($target, array $options = []): Distance
    {
        $strategyFactory = new StrategyFactory($options);
        return new Distance(
            $strategyFactory->create($target, new SpbKad(), new SpbKadJunctions()),
            $target
        );
    }

}
