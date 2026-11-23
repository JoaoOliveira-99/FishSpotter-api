<?php

namespace Tests\Feature;

use App\Enums\LicenseModality;
use App\Models\LicenseType;
use App\Models\Regulation;
use Database\Seeders\LicenseTypeSeeder;
use Database\Seeders\RegulationSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent_and_links_regulation(): void
    {
        $this->seed(RegulationSeeder::class);
        $this->seed(LicenseTypeSeeder::class);
        $this->seed(LicenseTypeSeeder::class);

        $this->assertSame(3, LicenseType::count());
        $this->assertSame(
            'portaria-484-2016',
            LicenseType::where('slug', 'pesca-apeada')->first()->regulation->slug,
        );
    }

    public function test_index_lists_licenses_ordered_by_sort_order(): void
    {
        LicenseType::factory()->create(['slug' => 'segunda', 'sort_order' => 2]);
        LicenseType::factory()->create(['slug' => 'primeira', 'sort_order' => 1]);

        $this->getJson('/api/licenses')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'primeira')
            ->assertJsonPath('data.1.slug', 'segunda')
            ->assertJsonStructure(['data' => [[
                'slug', 'name', 'modality', 'modality_label', 'summary', 'requirements',
                'validity_info', 'fee_info', 'official_url', 'verified_at',
                'regulation' => ['slug', 'reference', 'title', 'verified_at'],
            ]]]);
    }

    public function test_index_filters_by_modality(): void
    {
        LicenseType::factory()->create(['modality' => LicenseModality::Apeada]);
        LicenseType::factory()->create(['modality' => LicenseModality::Submarina]);

        $this->getJson('/api/licenses?modality=submarina')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.modality', 'submarina');
    }

    public function test_index_rejects_invalid_modality(): void
    {
        $this->getJson('/api/licenses?modality=voadora')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonValidationErrors('modality');
    }

    public function test_show_returns_license_with_regulation(): void
    {
        $regulation = Regulation::factory()->create(['reference' => 'Portaria n.º 484/2016']);
        LicenseType::factory()->create([
            'slug' => 'pesca-apeada',
            'requirements' => ['Requisito A', 'Requisito B'],
            'regulation_id' => $regulation->id,
        ]);

        $this->getJson('/api/licenses/pesca-apeada')
            ->assertOk()
            ->assertJsonPath('data.requirements', ['Requisito A', 'Requisito B'])
            ->assertJsonPath('data.regulation.reference', 'Portaria n.º 484/2016')
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.sort_order');
    }

    public function test_show_returns_null_regulation_when_not_linked(): void
    {
        LicenseType::factory()->withoutRegulation()->create(['slug' => 'sem-legislacao']);

        $this->getJson('/api/licenses/sem-legislacao')
            ->assertOk()
            ->assertJsonPath('data.regulation', null);
    }

    public function test_soft_deleted_license_is_hidden(): void
    {
        LicenseType::factory()->create(['slug' => 'antiga'])->delete();

        $this->getJson('/api/licenses')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/licenses/antiga')->assertNotFound()->assertJsonPath('code', 'not_found');
    }

    public function test_regulation_in_use_cannot_be_deleted(): void
    {
        $license = LicenseType::factory()->create();

        $this->expectException(QueryException::class);

        $license->regulation->delete();
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/licenses/nao-existe')->assertNotFound();
    }
}
