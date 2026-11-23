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
        Schema::create('spot_fishing_types', function (Blueprint $table) {
            $table->foreignId('spot_id')->constrained('fishing_spots')->cascadeOnDelete();
            $table->foreignId('fishing_type_id')->constrained()->restrictOnDelete();

            $table->primary(['spot_id', 'fishing_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spot_fishing_types');
    }
};
