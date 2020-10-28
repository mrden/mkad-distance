<?php


namespace Mrden\MkadDistance\Cache;

use Desarrolla2\Cache\Exception\InvalidArgumentException;
use Desarrolla2\Cache\Predis;

class DistancePredis extends Predis
{
    /**
     * Validate the key
     *
     * @param string $key
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertKey($key): void
    {
        if (!is_string($key)) {
            $type = (is_object($key) ? get_class($key) . ' ' : '') . gettype($key);
            throw new InvalidArgumentException("Expected key to be a string, not $type");
        }

        if ($key === '' || preg_match('~[{}()/\\\\@]~', $key)) {
            throw new InvalidArgumentException("Invalid key '$key'");
        }
    }
}