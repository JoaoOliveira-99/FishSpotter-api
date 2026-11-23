<?php

namespace Tests\Feature;

use App\Enums\Abundance;
use App\Enums\ContentStatus;
use App\Enums\SpeciesNameKind;
use App\Enums\SpotFrequency;
use App\Models\Bait;
use App\Models\FishingSpot;
use App\Models\FishingType;
use App\Models\Regulation;
use App\Models\Species;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SpeciesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SpeciesTest extends TestCase
{
    use RefreshDatabase;

    // ─── Regras de negócio ──────────────────────────────────────────────

    public function test_min_legal_size_requires_regulation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Species::factory()->create(['min_legal_size_cm' => 15.0, 'min_legal_size_regulation_id' => null]);
    }

    public function test_published_species_with_legal_info_must_be_verified(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Species::factory()->withMinLegalSize()->create(['verified_at' => null]);
    }

    public function test_draft_species_with_legal_info_can_wait_for_verification(): void
    {
        $species = Species::factory()->draft()->withMinLegalSize()->create(['verified_at' => null]);

        $this->assertSame(ContentStatus::Draft, $species->status);
    }

    public function test_capture_prohibited_also_requires_verification_to_publish(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Species::factory()->create(['capture_prohibited' => true, 'verified_at' => null]);
    }

    public function test_depth_range_must_be_consistent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Species::factory()->create(['depth_min_m' => 100, 'depth_max_m' => 10]);
    }

    public function test_month_must_be_between_1_and_12(): void
    {
        $species = Species::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $species->months()->create(['month' => 13, 'abundance' => Abundance::Alta]);
    }

    // ─── Seeders ────────────────────────────────────────────────────────

    public function test_seeder_is_idempotent_and_links_everything(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(SpeciesSeeder::class);

        $sargo = Species::where('slug', 'sargo')->first();

        $this->assertSame(6, Species::count());
        $this->assertSame(2, $sargo->names()->count());
        $this->assertTrue($sargo->baits()->exists());
        $this->assertTrue($sargo->fishingTypes()->exists());
        $this->assertSame('comum', $sargo->spots()->where('slug', 'molhe-da-pontinha')->first()->pivot->frequency);
    }

    // ─── API ────────────────────────────────────────────────────────────

    public function test_index_lists_only_published_species(): void
    {
        Species::factory()->count(2)->create();
        Species::factory()->draft()->create();

        $this->getJson('/api/species')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['slug', 'common_name', 'scientific_name', 'family', 'max_size_cm', 'min_legal_size_cm', 'capture_prohibited']]]);
    }

    public function test_search_matches_common_scientific_and_local_names(): void
    {
        $sargo = Species::factory()->create(['common_name' => 'Sargo', 'scientific_name' => 'Diplodus sargus']);
        $sargo->names()->create(['name' => 'White seabream', 'kind' => SpeciesNameKind::En]);
        Species::factory()->create(['common_name' => 'Moreia', 'scientific_name' => 'Muraena helena']);

        $this->getJson('/api/species?q=SARGO')->assertJsonCount(1, 'data')->assertJsonPath('data.0.common_name', 'Sargo');
        $this->getJson('/api/species?q=diplodus')->assertJsonCount(1, 'data')->assertJsonPath('data.0.common_name', 'Sargo');
        $this->getJson('/api/species?q=seabream')->assertJsonCount(1, 'data')->assertJsonPath('data.0.common_name', 'Sargo');
    }

    public function test_index_filters_by_month(): void
    {
        Species::factory()->create(['slug' => 'de-verao'])->months()->create(['month' => 7, 'abundance' => Abundance::Alta]);
        Species::factory()->create(['slug' => 'de-inverno'])->months()->create(['month' => 1, 'abundance' => Abundance::Alta]);

        $this->getJson('/api/species?month=7')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'de-verao');
    }

    public function test_index_filters_by_fishing_type_and_bait(): void
    {
        $spinning = FishingType::create(['slug' => 'spinning', 'name' => 'Spinning']);
        $lula = Bait::create(['slug' => 'lula', 'name' => 'Lula', 'kind' => 'natural']);

        $a = Species::factory()->create(['slug' => 'a']);
        $a->fishingTypes()->attach($spinning);
        $b = Species::factory()->create(['slug' => 'b']);
        $b->baits()->attach($lula);

        $this->getJson('/api/species?fishing_type=spinning')->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'a');
        $this->getJson('/api/species?bait=lula')->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'b');
    }

    public function test_index_rejects_invalid_filters(): void
    {
        $this->getJson('/api/species?month=13&fishing_type=nao-existe')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['month', 'fishing_type']);
    }

    public function test_show_returns_full_species(): void
    {
        $regulation = Regulation::factory()->create(['reference' => 'Portaria n.º 484/2016']);
        $species = Species::factory()->create([
            'slug' => 'sargo',
            'min_legal_size_cm' => 15.0,
            'min_legal_size_regulation_id' => $regulation->id,
        ]);
        $species->names()->create(['name' => 'White seabream', 'kind' => SpeciesNameKind::En]);
        $species->months()->createMany([
            ['month' => 8, 'abundance' => Abundance::Alta],
            ['month' => 2, 'abundance' => Abundance::Baixa],
        ]);
        $species->baits()->attach(Bait::create(['slug' => 'camarao', 'name' => 'Camarão', 'kind' => 'natural']), ['notes' => 'Vivo']);
        $species->fishingTypes()->attach(FishingType::create(['slug' => 'fundo', 'name' => 'Pesca de fundo']), ['notes' => 'Junto às pedras']);

        $this->getJson('/api/species/sargo')
            ->assertOk()
            ->assertJsonPath('data.names.0.name', 'White seabream')
            ->assertJsonPath('data.legal.min_legal_size_cm', 15)
            ->assertJsonPath('data.legal.regulation.reference', 'Portaria n.º 484/2016')
            ->assertJsonPath('data.season.0.month', 2) // ordenado por mês
            ->assertJsonPath('data.season.1.abundance', 'alta')
            ->assertJsonPath('data.baits.0.notes', 'Vivo')
            ->assertJsonPath('data.fishing_types.0.notes', 'Junto às pedras')
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.status');
    }

    public function test_show_hides_draft_and_deleted_species(): void
    {
        Species::factory()->draft()->create(['slug' => 'rascunho']);
        Species::factory()->create(['slug' => 'apagada'])->delete();

        $this->getJson('/api/species/rascunho')->assertNotFound()->assertJsonPath('code', 'not_found');
        $this->getJson('/api/species/apagada')->assertNotFound();
    }

    public function test_species_spots_lists_published_spots_with_frequency(): void
    {
        $species = Species::factory()->create(['slug' => 'sargo']);
        $species->spots()->attach(FishingSpot::factory()->create(['slug' => 'pontinha']), ['frequency' => SpotFrequency::Comum->value]);
        $species->spots()->attach(FishingSpot::factory()->draft()->create(), ['frequency' => SpotFrequency::Rara->value]);

        $this->getJson('/api/species/sargo/spots')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'pontinha')
            ->assertJsonPath('data.0.frequency', 'comum');
    }

    public function test_spot_species_lists_published_species_with_frequency(): void
    {
        $spot = FishingSpot::factory()->create(['slug' => 'pontinha']);
        $spot->species()->attach(Species::factory()->create(['slug' => 'sargo']), ['frequency' => SpotFrequency::Ocasional->value]);
        $spot->species()->attach(Species::factory()->draft()->create(), ['frequency' => SpotFrequency::Comum->value]);

        $this->getJson('/api/spots/pontinha/species')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'sargo')
            ->assertJsonPath('data.0.frequency', 'ocasional');
    }

    public function test_frequency_is_not_shown_in_normal_lists(): void
    {
        FishingSpot::factory()->create();
        Species::factory()->create();

        $this->getJson('/api/spots')->assertJsonMissingPath('data.0.frequency');
        $this->getJson('/api/species')->assertJsonMissingPath('data.0.frequency');
    }
}
