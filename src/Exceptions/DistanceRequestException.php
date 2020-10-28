<?php

namespace Mrden\MkadDistance\Exceptions;

use Throwable;

class DistanceRequestException extends DistanceExceptions
{
    /**
     * @var float
     */
    private $lineDistance = 0.0;

    /**
     * DistanceRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param $lineDistance
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $lineDistance = 0.0)
    {
        $this->lineDistance = $lineDistance;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return float
     */
    public function getLineDistance()
    {
        return $this->lineDistance;
    }
}