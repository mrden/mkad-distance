<?php

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getYandexGeoCoderApiKey(): string
    {
        return '606b6dc0-6c97-4335-b9da-0a8140288a8a';
    }
}