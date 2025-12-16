<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();

            $table->string('email');
            $table->string('token')->unique(); // token de convite

            $table->unsignedInteger('credits_granted')->default(3);

            $table->foreignId('invited_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps(); // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
