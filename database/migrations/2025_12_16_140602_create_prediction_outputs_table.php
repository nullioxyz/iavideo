<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_outputs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prediction_id')
                ->constrained('predictions')
                ->cascadeOnDelete();

            $table->enum('kind', ['video', 'thumbnail', 'gif'])->default('video');

            $table->string('path');

            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->timestamps();

            // índices úteis
            $table->index(['prediction_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_outputs');
    }
};
