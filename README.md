# Расчет расстояния пути за МКАД (или КАД в Санкт-Петербурге) на PHP (по дорогам)

Данная библиотека позволяет рассчитать расстояние пути от МКАД (КАД) до адреса, города и т.д. Целевой пункт назначения можно указать координатами (долгота, широта) или просто текстом.

## Установка

`composer require mrden/mkad-distance`

## Примеры использования

```php
use Mrden\MkadDistance\Distance;
// Расчет по массиву координат
$distance = Distance::calculateByRouteToMoscowMkad(
    [55.860297, 37.120094]
)->calculate();

// Расчет по экземпляру класса \Mrden\MkadDistance\Geometry\Point
$distance = Distance::calculateByRouteToMoscowMkad(
    new \Mrden\MkadDistance\Geometry\Point(55.860297, 37.120094)
)->calculate();

// Расчет по текстовому названию
$distance = Distance::calculateByRouteSpbKadCalculator(
    'Санкт-Петербург, посёлок Песочный, Советская улица, 34/21',
    ['yandexGeoCoderApiKey' => 'YOUR_TOKEN']   
)->calculate();

// Cache
$cache = new AnySimpleCacheInterfaceRealisation();
$distance = Distance::calculateByRouteToMoscowMkad(
    new \Mrden\MkadDistance\Geometry\Point(55.860297, 37.120094),
    ['cache' => $cache]
)->calculate();

```

