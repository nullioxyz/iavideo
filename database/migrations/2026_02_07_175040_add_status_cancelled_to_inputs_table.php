<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE inputs 
                MODIFY status ENUM(
                    'created',
                    'processing',
                    'done',
                    'failed',
                    'cancelled'
                ) NOT NULL DEFAULT 'created'
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE inputs 
                MODIFY status ENUM(
                    'created',
                    'processing',
                    'done',
                    'failed'
                ) NOT NULL DEFAULT 'created'
            ");
        });
    }
};
