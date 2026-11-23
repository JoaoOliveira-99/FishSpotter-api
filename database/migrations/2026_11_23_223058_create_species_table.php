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
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('common_name', 120);
            $table->string('scientific_name', 160)->unique();
            $table->string('family', 80)->nullable();
            $table->integer('worms_aphia_id')->nullable(); // identificador WoRMS
            $table->text('description')->nullable();
            $table->text('habitat')->nullable();
            $table->smallInteger('depth_min_m')->nullable();
            $table->smallInteger('depth_max_m')->nullable();
            $table->smallInteger('max_size_cm')->nullable();
            $table->decimal('min_legal_size_cm', 5, 1)->nullable();
            $table->foreignId('min_legal_size_regulation_id')->nullable()->constrained('regulations')->restrictOnDelete();
            $table->boolean('capture_prohibited')->default(false);
            $table->text('safety_notes')->nullable();
            $table->string('status', 12)->default('draft');
            $table->timestampTz('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
