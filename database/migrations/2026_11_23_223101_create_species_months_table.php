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
        Schema::create('species_months', function (Blueprint $table) {
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1–12
            $table->string('abundance', 8); // baixa | media | alta

            $table->primary(['species_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species_months');
    }
};
