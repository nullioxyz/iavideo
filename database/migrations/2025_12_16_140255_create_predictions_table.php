<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('input_id')
                ->constrained('inputs')
                ->cascadeOnDelete();

            $table->foreignId('model_id')
                ->constrained('models')
                ->restrictOnDelete();

            $table->string('external_id')->nullable()->unique();

            $table->enum('status', ['queued', 'starting', 'submitting', 'processing', 'succeeded', 'failed', 'cancelled', 'refunded'])->default('queued');

            $table->enum('source', ['web', 'admin', 'api'])->default('web');

            $table->unsignedInteger('attempt')->default(1);

            $table->foreignId('retry_of_prediction_id')
                ->nullable()
                ->constrained('predictions')
                ->nullOnDelete();

            // tempo
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // duração / métricas de tempo
            $table->unsignedInteger('duration_seconds')->nullable(); // ex: 5
            $table->unsignedInteger('processing_ms')->nullable();
            $table->unsignedInteger('total_ms')->nullable();

            // custo
            $table->decimal('cost_estimate_usd', 10, 4)->nullable();
            $table->decimal('cost_actual_usd', 10, 4)->nullable();

            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            $table->timestamps();

            $table->index(['input_id', 'attempt']);
            $table->index(['status', 'created_at']);
            $table->index(['model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
