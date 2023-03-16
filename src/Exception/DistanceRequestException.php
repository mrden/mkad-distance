<?php

namespace Mrden\MkadDistance\Exception;

use Mrden\MkadDistance\Geometry\DistanceBetweenPoints;
use Throwable;

class DistanceRequestException extends DistanceException
{
    /**
     * @var DistanceBetweenPoints|null
     */
    private $lineDistance;

    /**
     * DistanceRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param DistanceBetweenPoints|null $lineDistance
     */
    public function __construct(
        $message = '',
        $code = 0,
        Throwable $previous = null,
        DistanceBetweenPoints $lineDistance = null
    ) {
        $this->lineDistance = $lineDistance;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return DistanceBetweenPoints|null
     */
    public function getLineDistance(): ?DistanceBetweenPoints
    {
        return $this->lineDistance;
    }
}
