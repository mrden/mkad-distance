<?php

namespace Mrden\MkadDistance\Strategy;

use Exception;
use InvalidArgumentException;
use Mrden\MkadDistance\Exception\DistanceRequestException;
use Mrden\MkadDistance\Exception\InnerPolygonException;
use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Iterface\DistanceCalculatorStrategy;
use Psr\SimpleCache\CacheException;

class PointDistanceCalculator extends AbstractDistanceCalculate implements DistanceCalculatorStrategy
{
    /**
     * Лимит ближайших (по расстоянию по прямой) развязок для расчета расстояний от них
     */
    private const JUNCTIONS_LIMIT = 6;

    /**
     * @param Point $target
     * @param bool $calcByRoutes
     * @return DistanceBetweenPoints
     * @throws DistanceRequestException
     * @throws InnerPolygonException
     */
    public function calculate($target, bool $calcByRoutes = true): DistanceBetweenPoints
    {
        if (!$target instanceof Point) {
            throw new InvalidArgumentException(
                sprintf('Target param most be %s type', Point::class)
            );
        }
        $isInner = $this->basePolygon->isInner($target);

        if ($isInner) {
            throw new InnerPolygonException(
                sprintf('Target point located inside the %s.', $this->basePolygon)
            );
        }

        $lineDistancesToJunctions = [];
        // Процесс отсечения самых дальних точек развязок на МКАД
        foreach ($this->junctionsPolygon->getVertices() as $vertex) {
            $lineDistancesToJunctions[] = $this->calculateLineDistance($vertex, $target);
        }
        // Расстояние по прямой от целевой точки до самой ближайшей развязки на МКАД
        $minLineDistance = $this->findMinDistance($lineDistancesToJunctions);
        $routeDistancesToJunctions = [];

        if (!$calcByRoutes) {
            return $minLineDistance;
        }

        // Сортируем массив расстояний до развязок по возрастанию
        usort($lineDistancesToJunctions, function (DistanceBetweenPoints $distance1, DistanceBetweenPoints $distance2) {
            if ($distance1->getDistance() == $distance2->getDistance()) {
                return 0;
            }
            return ($distance1->getDistance() < $distance2->getDistance()) ? -1 : 1;
        });
        $current = 0;
        foreach ($lineDistancesToJunctions as $lineDistance) {
            try {
                $routeDistancesToJunctions[] = $this->calculateRouteDistance(
                    $lineDistance->getFrom(),
                    $lineDistance->getTo()
                );
            } catch (Exception|CacheException $e) {
                throw new DistanceRequestException($e->getMessage(), $e->getCode(), $e, $minLineDistance);
            }
            $current++;
            if ($current >= self::JUNCTIONS_LIMIT) {
                break;
            }
        }

        return $this->findMinDistance($routeDistancesToJunctions);
    }
}
