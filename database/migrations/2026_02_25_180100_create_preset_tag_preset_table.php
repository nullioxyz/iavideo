<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preset_tag_preset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preset_id')->constrained('presets')->cascadeOnDelete();
            $table->foreignId('preset_tag_id')->constrained('preset_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['preset_id', 'preset_tag_id']);
            $table->index(['preset_tag_id', 'preset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preset_tag_preset');
    }
};

