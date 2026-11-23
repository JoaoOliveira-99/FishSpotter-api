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
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 140)->unique();
            $table->string('reference', 120)->unique(); 
            $table->string('title', 255);
            $table->text('summary')->nullable();
            $table->text('url')->nullable();
            $table->date('published_on')->nullable();
            $table->timestampTz('verified_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
