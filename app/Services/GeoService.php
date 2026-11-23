<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Todos os cálculos geográficos da app passam por aqui.
 * Se um dia se mudar para PostGIS, só esta classe muda.
 *
 * GeoJSON usa a ordem [longitude, latitude].
 */
class GeoService
{
    /**
     * @param  array<string, mixed>  $geometry  GeoJSON Polygon ou MultiPolygon
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    public static function boundingBox(array $geometry): array
    {
        $points = self::points($geometry);

        $lngs = array_column($points, 0);
        $lats = array_column($points, 1);

        return [
            'min_lat' => min($lats),
            'max_lat' => max($lats),
            'min_lng' => min($lngs),
            'max_lng' => max($lngs),
        ];
    }

    /**
     * @param  array<string, mixed>  $geometry
     * @return list<array{0: float, 1: float}>
     */
    private static function points(array $geometry): array
    {
        $polygons = match ($geometry['type'] ?? null) {
            'Polygon' => [$geometry['coordinates'] ?? null],
            'MultiPolygon' => $geometry['coordinates'] ?? null,
            default => throw new InvalidArgumentException('A geometria tem de ser um Polygon ou MultiPolygon GeoJSON.'),
        };

        if (! is_array($polygons) || $polygons === []) {
            throw new InvalidArgumentException('A geometria não tem coordenadas.');
        }

        $points = [];

        foreach ($polygons as $rings) {
            foreach ((array) $rings as $ring) {
                if (! is_array($ring) || count($ring) < 4) {
                    throw new InvalidArgumentException('Cada anel do polígono precisa de pelo menos 4 pontos.');
                }

                foreach ($ring as $point) {
                    $points[] = self::validPoint($point);
                }
            }
        }

        return $points;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function validPoint(mixed $point): array
    {
        if (! is_array($point) || ! is_numeric($point[0] ?? null) || ! is_numeric($point[1] ?? null)) {
            throw new InvalidArgumentException('Ponto inválido: esperado [longitude, latitude].');
        }

        [$lng, $lat] = [(float) $point[0], (float) $point[1]];

        if ($lng < -180 || $lng > 180 || $lat < -90 || $lat > 90) {
            throw new InvalidArgumentException("Coordenadas fora do intervalo: [{$lng}, {$lat}].");
        }

        return [$lng, $lat];
    }
}
