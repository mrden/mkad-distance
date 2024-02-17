<?php

namespace Mrden\MkadDistance\Geometry;

abstract class Polygon implements \Stringable
{
    /**
     * @var list<Point>
     */
    private array $vertices = [];

    /**
     * Polygon constructor.
     * @param list<Point>|list<array{0: float, 1: float}> $vertices
     */
    public function __construct(array $vertices = [])
    {
        if (empty($vertices)) {
            return;
        }

        foreach ($vertices as $key => $vertex) {
            $res = $vertex;
            if (!$vertex instanceof Point) {
                $res = Point::createFromArray($vertex);
            }
            $this->vertices[$key] = $res;
        }
    }

    /**
     * @return list<Point>
     */
    public function getVertices(): array
    {
        return $this->vertices;
    }

    public function pointOnVertex(Point $point): bool
    {
        foreach ($this->vertices as $vertex) {
            if (Point::compare($point, $vertex)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Попадает ли точка внутрь полигона
     */
    public function isInner(Point $point): bool
    {
        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex($point) === true) {
            return true;
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = \count($this->vertices);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertexPrev = $this->vertices[$i - 1];
            $vertexCur = $this->vertices[$i];
            if ($vertexPrev->getLon() == $vertexCur->getLon() && $vertexPrev->getLon() == $point->getLon() &&
                $point->getLat() > min($vertexPrev->getLat(), $vertexCur->getLat()) &&
                $point->getLat() < max($vertexPrev->getLat(), $vertexCur->getLat())
            ) { // Check if point is on an horizontal polygon boundary
                return true;
            }
            if ($point->getLon() > min($vertexPrev->getLon(), $vertexCur->getLon()) &&
                $point->getLon() <= max($vertexPrev->getLon(), $vertexCur->getLon()) &&
                $point->getLat() <= max($vertexPrev->getLat(), $vertexCur->getLat()) &&
                $vertexPrev->getLon() != $vertexCur->getLon()
            ) {
                $xinters = ($point->getLon() - $vertexPrev->getLon()) * ($vertexCur->getLat() - $vertexPrev->getLat()) / ($vertexCur->getLon() - $vertexPrev->getLon()) + $vertexPrev->getLat();
                if ($xinters == $point->getLat()) { // Check if point is on the polygon boundary (other than horizontal)
                    return true;
                }
                if ($vertexPrev->getLat() == $vertexCur->getLat() || $point->getLat() <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        return $intersections % 2 != 0;
    }
}
