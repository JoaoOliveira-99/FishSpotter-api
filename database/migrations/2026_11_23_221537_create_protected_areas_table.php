<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('protected_areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 120);
            $table->jsonb('boundary'); // GeoJSON Polygon ou MultiPolygon, [lng, lat]
            $table->decimal('min_lat', 8, 6);
            $table->decimal('max_lat', 8, 6);
            $table->decimal('min_lng', 9, 6);
            $table->decimal('max_lng', 9, 6);
            $table->string('restriction_level', 12);
            $table->text('restrictions');
            $table->foreignId('regulation_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestampTz('verified_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['min_lat', 'max_lat', 'min_lng', 'max_lng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protected_areas');
    }
};
