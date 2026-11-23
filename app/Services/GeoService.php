<?php

namespace App\Services;

use App\Enums\Coast;
use App\Models\ForecastPoint;
use App\Models\ProtectedArea;
use InvalidArgumentException;
use Location\Coordinate;
use Location\Distance\Haversine;
use Location\Polygon;

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
        $points = array_merge(...array_merge(...self::polygons($geometry)));

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
     * O ponto está dentro do polígono? Respeita buracos (anéis interiores).
     *
     * @param  array<string, mixed>  $geometry
     */
    public static function contains(array $geometry, float $lat, float $lng): bool
    {
        $point = new Coordinate($lat, $lng);

        foreach (self::polygons($geometry) as $rings) {
            [$outer, $holes] = [array_shift($rings), $rings];

            if (! self::ringContains($outer, $point)) {
                continue;
            }

            foreach ($holes as $hole) {
                if (self::ringContains($hole, $point)) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Distância em metros entre dois pontos.
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return (new Coordinate($lat1, $lng1))->getDistance(new Coordinate($lat2, $lng2), new Haversine);
    }

    /**
     * Área protegida onde o ponto se encontra (ou null).
     */
    public static function protectedAreaAt(float $lat, float $lng): ?ProtectedArea
    {
        // O retângulo envolvente descarta logo as áreas longe; o polígono decide
        return ProtectedArea::query()
            ->where('min_lat', '<=', $lat)
            ->where('max_lat', '>=', $lat)
            ->where('min_lng', '<=', $lng)
            ->where('max_lng', '>=', $lng)
            ->get()
            ->first(fn (ProtectedArea $area) => self::contains($area->boundary, $lat, $lng));
    }

    /**
     * Ponto de previsão ativo mais próximo, de preferência na mesma costa.
     */
    public static function nearestForecastPoint(float $lat, float $lng, ?Coast $coast = null): ?ForecastPoint
    {
        $points = ForecastPoint::active()->get();

        if ($coast !== null && $points->contains('coast', $coast)) {
            $points = $points->where('coast', $coast);
        }

        return $points->sortBy(fn (ForecastPoint $p) => self::distance($lat, $lng, $p->latitude, $p->longitude))->first();
    }

    public static function assertCoordinate(float $lat, float $lng): void
    {
        self::validPoint([$lng, $lat]);
    }

    /**
     * @param  array<string, mixed>  $geometry
     * @return list<list<list<array{0: float, 1: float}>>>  polígonos → anéis → pontos [lng, lat]
     */
    private static function polygons(array $geometry): array
    {
        $polygons = match ($geometry['type'] ?? null) {
            'Polygon' => [$geometry['coordinates'] ?? null],
            'MultiPolygon' => $geometry['coordinates'] ?? null,
            default => throw new InvalidArgumentException('A geometria tem de ser um Polygon ou MultiPolygon GeoJSON.'),
        };

        if (! is_array($polygons) || $polygons === []) {
            throw new InvalidArgumentException('A geometria não tem coordenadas.');
        }

        $result = [];

        foreach ($polygons as $rings) {
            if (! is_array($rings) || $rings === []) {
                throw new InvalidArgumentException('Polígono sem anéis.');
            }

            $polygon = [];

            foreach ($rings as $ring) {
                if (! is_array($ring) || count($ring) < 4) {
                    throw new InvalidArgumentException('Cada anel do polígono precisa de pelo menos 4 pontos.');
                }

                $polygon[] = array_map(self::validPoint(...), $ring);
            }

            $result[] = $polygon;
        }

        return $result;
    }

    /**
     * @param  list<array{0: float, 1: float}>  $ring
     */
    private static function ringContains(array $ring, Coordinate $point): bool
    {
        $polygon = new Polygon;

        foreach ($ring as [$lng, $lat]) {
            $polygon->addPoint(new Coordinate($lat, $lng));
        }

        return $polygon->contains($point);
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
