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
        Schema::create('license_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 120);
            $table->string('modality', 12);
            $table->text('summary');
            $table->jsonb('requirements')->default('[]');
            $table->text('validity_info')->nullable();
            $table->text('fee_info')->nullable();
            $table->text('official_url');
            $table->foreignId('regulation_id')->nullable()->constrained()->restrictOnDelete();
            $table->smallInteger('sort_order')->default(0);
            $table->timestampTz('verified_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_types');
    }
};
