<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preset_tag_langs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('preset_tag_id')->constrained('preset_tags')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['preset_tag_id', 'language_id']);
            $table->index(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preset_tag_langs');
    }
};

