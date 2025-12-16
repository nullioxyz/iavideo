<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('prediction_id')
                ->constrained('predictions')
                ->cascadeOnDelete();

            // 1..5
            $table->unsignedTinyInteger('rating');

            $table->text('comment')->nullable();

            // apenas created_at
            $table->timestamp('created_at')->useCurrent();

            // garante 1 feedback por prediction (recomendado)
            $table->unique('prediction_id');

            // índices úteis
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
