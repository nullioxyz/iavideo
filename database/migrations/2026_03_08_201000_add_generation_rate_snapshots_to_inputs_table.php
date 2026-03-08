<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inputs', function (Blueprint $table): void {
            $table->decimal('model_cost_per_second_usd', 10, 4)->nullable()->after('estimated_cost_usd');
            $table->decimal('model_credits_per_second', 10, 4)->nullable()->after('model_cost_per_second_usd');
        });

        DB::statement('
            UPDATE inputs
            INNER JOIN models ON models.id = inputs.model_id
            SET inputs.model_cost_per_second_usd = models.cost_per_second_usd,
                inputs.model_credits_per_second = models.credits_per_second
            WHERE inputs.model_id IS NOT NULL
              AND inputs.model_cost_per_second_usd IS NULL
              AND inputs.model_credits_per_second IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('inputs', function (Blueprint $table): void {
            $table->dropColumn([
                'model_cost_per_second_usd',
                'model_credits_per_second',
            ]);
        });
    }
};
