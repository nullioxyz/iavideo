<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('language_id')
                ->nullable()
                ->after('phone_number_verified_at')
                ->constrained('languages')
                ->nullOnDelete();

            $table->string('country_code', 2)
                ->nullable()
                ->after('language_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('language_id');
            $table->dropColumn('country_code');
        });
    }
};

