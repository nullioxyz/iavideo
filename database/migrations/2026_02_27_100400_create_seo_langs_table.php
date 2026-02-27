<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_langs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seo_id')->constrained('seos')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('twitter_title')->nullable();
            $table->string('twitter_description')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['seo_id', 'language_id']);
            $table->index(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_langs');
    }
};

