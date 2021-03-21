<?php


namespace Mrden\MkadDistance\Route;

class Route extends \OSRM\Service\Route
{
    /**
     * @param null $coordinates
     */
    public function setCoordinates($coordinates): void
    {
        $this->coordinates = $coordinates;
    }
}