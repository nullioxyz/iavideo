<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutionals', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index(['active', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutionals');
    }
};

