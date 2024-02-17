<?php

namespace Mrden\MkadDistance\Calculator;

use Mrden\MkadDistance\Exception\DistanceException;
use Mrden\MkadDistance\Exception\DistanceRequestException;
use Mrden\MkadDistance\Exception\InnerPolygonException;
use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Mrden\MkadDistance\Geometry\Point;
use Mrden\MkadDistance\Contracts\DistanceCalculator;
use Psr\SimpleCache\InvalidArgumentException as CacheInvalidArgumentException;

class PointDistanceCalculator extends AbstractDistanceCalculate implements DistanceCalculator
{
    /**
     * Лимит ближайших (по расстоянию по прямой) развязок для расчета расстояний от них
     */
    private const JUNCTIONS_LIMIT = 6;

    /**
     * @param Point|array{0: float, 1: float}|string $target
     * @throws DistanceRequestException
     * @throws InnerPolygonException
     * @throws DistanceException
     */
    public function calculate(Point|array|string $target, bool $calcByRoutes = true): DistanceBetweenPoints
    {
        if (!$target instanceof Point) {
            throw new \InvalidArgumentException(
                \sprintf('Target param must be %s type', Point::class)
            );
        }
        $isInner = $this->basePolygon->isInner($target);

        if ($isInner) {
            throw new InnerPolygonException(
                \sprintf('Target point located inside the %s.', $this->basePolygon)
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
            $result = $minLineDistance;
        } else {
            // Сортируем массив расстояний до развязок по возрастанию
            \usort($lineDistancesToJunctions, function (DistanceBetweenPoints $distance1, DistanceBetweenPoints $distance2) {
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
                } catch (\Exception|CacheInvalidArgumentException $e) {
                    throw new DistanceRequestException($e->getMessage(), $e->getCode(), $e, $minLineDistance);
                }
                $current++;
                if ($current >= self::JUNCTIONS_LIMIT) {
                    break;
                }
            }
            $result = $this->findMinDistance($routeDistancesToJunctions);
        }

        if (!$result) {
            throw new DistanceException('Error calculate');
        }

        return $this->findMinDistance($routeDistancesToJunctions);
    }
}
