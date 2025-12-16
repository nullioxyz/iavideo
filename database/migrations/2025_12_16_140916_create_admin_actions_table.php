<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('action'); // Nome da ação (ex: "create", "update", "delete", etc.)
            $table->string('target_type')->nullable(); // Tipo de entidade (ex: 'user', 'preset', etc.)
            $table->unsignedBigInteger('target_id')->nullable(); // ID da entidade alvo (ex: 'user_id', 'preset_id', etc.)
            $table->json('metadata')->nullable(); // Armazena dados adicionais (ex: parâmetros usados, motivos, etc.)

            // Timestamp
            $table->timestamp('created_at')->useCurrent();

            // Índices úteis
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_actions');
    }
};
