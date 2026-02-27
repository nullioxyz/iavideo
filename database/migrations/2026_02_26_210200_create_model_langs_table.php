<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_langs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['model_id', 'language_id']);
            $table->index(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_langs');
    }
};

