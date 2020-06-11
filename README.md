# Расчет расстояния пути за МКАД на PHP (по дорогам)

Данная библиотека позволяет рассчитать расстояние пути от МКАД до адреса, города и т.д. Целевой пункт назначения можно указать координатами (долгота, широта) или просто текстом.

## Установка

`composer require mrden/mkad-distance`

## Примеры использования

```php
use Mrden\MkadDistance\Distance;
$distance = new Distance();
```
```php
$res = $distance->calculate([55.860297, 37.120094]);
```
или
```php
$distance->setYandexGeoCoderApiKey('YOUR_TOKEN');
$res = $distance->calculate('Московская область, городской округ Истра, Дедовск, улица Гагарина, 14');
```
