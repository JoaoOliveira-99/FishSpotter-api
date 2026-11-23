<?php

namespace Tests\Unit;

use App\Services\GeoService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GeoServiceTest extends TestCase
{
    public function test_bounding_box_of_polygon(): void
    {
        $polygon = [
            'type' => 'Polygon',
            'coordinates' => [[
                [-16.875, 32.627],
                [-16.835, 32.627],
                [-16.835, 32.645],
                [-16.875, 32.645],
                [-16.875, 32.627],
            ]],
        ];

        $this->assertSame(
            ['min_lat' => 32.627, 'max_lat' => 32.645, 'min_lng' => -16.875, 'max_lng' => -16.835],
            GeoService::boundingBox($polygon),
        );
    }

    public function test_bounding_box_of_multipolygon_covers_all_parts(): void
    {
        $multi = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[[-17.0, 32.6], [-16.9, 32.6], [-16.9, 32.7], [-17.0, 32.6]]],
                [[[-16.4, 33.0], [-16.3, 33.0], [-16.3, 33.1], [-16.4, 33.0]]],
            ],
        ];

        $this->assertSame(
            ['min_lat' => 32.6, 'max_lat' => 33.1, 'min_lng' => -17.0, 'max_lng' => -16.3],
            GeoService::boundingBox($multi),
        );
    }

    public function test_rejects_non_polygon_geometry(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GeoService::boundingBox(['type' => 'Point', 'coordinates' => [-16.9, 32.6]]);
    }

    public function test_rejects_ring_with_too_few_points(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GeoService::boundingBox([
            'type' => 'Polygon',
            'coordinates' => [[[-16.9, 32.6], [-16.8, 32.6], [-16.9, 32.6]]],
        ]);
    }

    public function test_rejects_swapped_or_out_of_range_coordinates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // latitude 200 é impossível
        GeoService::boundingBox([
            'type' => 'Polygon',
            'coordinates' => [[[-16.9, 200], [-16.8, 32.6], [-16.8, 32.7], [-16.9, 200]]],
        ]);
    }
}
