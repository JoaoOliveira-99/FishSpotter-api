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
        Schema::create('species_fishing_types', function (Blueprint $table) {
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fishing_type_id')->constrained()->restrictOnDelete();
            $table->string('notes', 255)->nullable();

            $table->primary(['species_id', 'fishing_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species_fishing_types');
    }
};
