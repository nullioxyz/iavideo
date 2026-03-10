<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table): void {
            $table->boolean('default')->default(false)->after('public_visible');
        });
    }

    public function down(): void
    {
        Schema::table('models', function (Blueprint $table): void {
            $table->dropColumn('default');
        });
    }
};
