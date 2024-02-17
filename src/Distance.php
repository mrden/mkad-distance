<?php

namespace Mrden\MkadDistance;

use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Geometry\Polygon\MscMkad;
use Mrden\MkadDistance\Geometry\Polygon\MscMkadJunctions;
use Mrden\MkadDistance\Geometry\Polygon\SpbKad;
use Mrden\MkadDistance\Geometry\Polygon\SpbKadJunctions;
use Mrden\MkadDistance\Contracts\DistanceCalculator;
use Mrden\MkadDistance\Calculator\StrategyCalculatorFactory;

class Distance
{
    private DistanceCalculator $calculator;

    public function __construct(DistanceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Distance in kilometers
     * @param Point|array{0: float, 1: float}|string $target
     */
    public function calculate(Point|array|string $target, bool $calcByRoutes = true): float
    {
        return \round($this->calculator->calculate($target, $calcByRoutes)->getDistance() / 1000, 2);
    }

    /**
     * @param Point|array{0: float, 1: float}|string $target
     */
    public static function calculateByRouteToMoscowMkad(Point|array|string $target, array $options = []): float
    {
        $strategyFactory = new StrategyCalculatorFactory($options);
        return (new Distance($strategyFactory->create(
            $target,
            new MscMkad(),
            new MscMkadJunctions()
        )))->calculate($target);
    }

    /**
     * @param Point|array{0: float, 1: float}|string $target
     */
    public static function calculateByLineToMoscowMkad(Point|array|string $target, array $options = []): float
    {
        $strategyFactory = new StrategyCalculatorFactory($options);
        return (new Distance($strategyFactory->create(
            $target,
            new MscMkad(),
            new MscMkadJunctions()
        )))->calculate($target, false);
    }

    /**
     * @param Point|array{0: float, 1: float}|string $target
     */
    public static function calculateByRouteSpbKadCalculator(Point|array|string $target, array $options = []): float
    {
        $strategyFactory = new StrategyCalculatorFactory($options);
        return (new Distance($strategyFactory->create(
            $target,
            new SpbKad(),
            new SpbKadJunctions()
        )))->calculate($target);
    }

    /**
     * @param array{0: float, 1: float}|string|Point $target
     */
    public static function calculateByLineSpbKadCalculator(Point|array|string $target, array $options = []): float
    {
        $strategyFactory = new StrategyCalculatorFactory($options);
        return (new Distance($strategyFactory->create(
            $target,
            new SpbKad(),
            new SpbKadJunctions()
        )))->calculate($target, false);
    }
}
