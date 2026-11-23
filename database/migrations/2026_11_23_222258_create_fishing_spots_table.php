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
        Schema::create('fishing_spots', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 140)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->foreignId('spot_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('municipality_id')->constrained()->restrictOnDelete();
            $table->decimal('latitude', 8, 6);
            $table->decimal('longitude', 9, 6);
            $table->string('coast', 12);
            $table->smallInteger('exposure_deg')->nullable(); 
            $table->text('access_description')->nullable();
            $table->smallInteger('access_difficulty')->nullable();
            $table->boolean('has_parking')->nullable();
            $table->text('safety_notes')->nullable();
            $table->foreignId('forecast_point_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('protected_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 12)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['latitude', 'longitude']);
            $table->index(['status', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_spots');
    }
};
