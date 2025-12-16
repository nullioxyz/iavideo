<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presets', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // prompts e parâmetros controlados (sem prompt livre no MVP)
            $table->text('prompt');
            $table->text('negative_prompt')->nullable();

            $table->string('aspect_ratio')->default('16:9'); // 16:9, 9:16, 1:1
            $table->unsignedInteger('duration_seconds')->default(5); // MVP fixo 5

            // modelo padrão do preset
            $table->foreignId('default_model_id')
                ->constrained('models')
                ->restrictOnDelete();

            // custo estimado
            $table->decimal('cost_estimate_usd', 10, 4)->nullable();

            // preview to UI
            $table->string('preview_video_url')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            // índices úteis
            $table->index(['active']);
            $table->index(['default_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presets');
    }
};
