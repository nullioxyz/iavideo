<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutional_langs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institutional_id')->constrained('institutionals')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['institutional_id', 'language_id']);
            $table->index(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutional_langs');
    }
};

