<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inputs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('preset_id')
                ->constrained('presets')
                ->restrictOnDelete();

            // input armazenado no DO Spaces (privado)
            // (mesmo se você usar Spatie Media Library, guardar o "path/key" ajuda muito)
            $table->string('start_image_path');

            // metadata (auditoria)
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            // gestão de crédito por pedido
            $table->boolean('credit_debited')->default(false);

            $table->foreignId('credit_ledger_id')
                ->nullable()
                ->constrained('credit_ledger')
                ->nullOnDelete();

            // lifecycle do request
            $table->enum('status', ['created', 'processing', 'done', 'failed'])->default('created');

            $table->timestamps();

            // índices úteis
            $table->index(['user_id', 'created_at']);
            $table->index(['preset_id', 'created_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inputs');
    }
};
