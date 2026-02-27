<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preset_langs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('preset_id')->constrained('presets')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('prompt')->nullable();
            $table->text('negative_prompt')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['preset_id', 'language_id']);
            $table->index(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preset_langs');
    }
};

