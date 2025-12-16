<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();

            $table->foreignId('platform_id')
                ->constrained('platforms')
                ->cascadeOnDelete();

            $table->string('name'); // Kling v2.5 Turbo Pro
            $table->string('slug'); // ex: kwaivgi/kling-v2.5-turbo-pro

            // Replicate costuma usar version hash, se aplicável
            $table->string('version')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            // índices úteis
            $table->index(['platform_id', 'active']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};
