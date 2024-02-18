<?php

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return string
     * @throws \Exception
     */
    protected function getYandexGeoCoderApiKey(): string
    {
        $config = include __DIR__ . '/test.config.php';
        $token = $config['token'] ?? '';
        if (!$token) {
            throw new \Exception('not set yandexGeoCoderApiKey (see tests/test.config.php)');
        }
        return $token;
    }
}
