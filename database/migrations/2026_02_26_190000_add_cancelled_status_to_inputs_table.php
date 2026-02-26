<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE inputs MODIFY COLUMN status ENUM('created','processing','done','failed','cancelled') NOT NULL DEFAULT 'created'"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE inputs MODIFY COLUMN status ENUM('created','processing','done','failed') NOT NULL DEFAULT 'created'"
        );
    }
};

