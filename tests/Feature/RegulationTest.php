<?php

namespace Tests\Feature;

use App\Http\Resources\RegulationResource;
use App\Models\Regulation;
use Database\Seeders\RegulationSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class RegulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RegulationSeeder::class);
        $count = Regulation::count();

        $this->seed(RegulationSeeder::class);

        $this->assertGreaterThan(0, $count);
        $this->assertSame($count, Regulation::count());
    }

    public function test_seeder_does_not_run_in_production(): void
    {
        $this->app['env'] = 'production';

        // Chamada direta: o $this->seed() pede confirmação em produção
        $this->app->make(RegulationSeeder::class)->run();

        $this->assertSame(0, Regulation::count());
    }

    public function test_verified_at_is_required(): void
    {
        $this->expectException(QueryException::class);

        Regulation::create([
            'slug' => 'portaria-1-2020',
            'reference' => 'Portaria n.º 1/2020',
            'title' => 'Sem data de verificação',
        ]);
    }

    public function test_reference_is_unique(): void
    {
        Regulation::factory()->create(['reference' => 'Portaria n.º 484/2016']);

        $this->expectException(QueryException::class);

        Regulation::factory()->create(['reference' => 'Portaria n.º 484/2016']);
    }

    public function test_resource_exposes_dates_in_api_format(): void
    {
        $regulation = Regulation::factory()->create([
            'published_on' => '2016-12-30',
            'verified_at' => '2026-11-23 10:30:00',
        ]);

        $data = (new RegulationResource($regulation))->toArray(new Request);

        $this->assertSame('2016-12-30', $data['published_on']);
        $this->assertSame('2026-11-23T10:30:00Z', $data['verified_at']);
        $this->assertArrayNotHasKey('id', $data);
    }

    public function test_resource_handles_missing_optional_fields(): void
    {
        $regulation = Regulation::factory()->create([
            'summary' => null,
            'url' => null,
            'published_on' => null,
        ]);

        $data = (new RegulationResource($regulation))->toArray(new Request);

        $this->assertNull($data['summary']);
        $this->assertNull($data['url']);
        $this->assertNull($data['published_on']);
    }
}
