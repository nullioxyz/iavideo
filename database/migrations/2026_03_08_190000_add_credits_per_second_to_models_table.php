<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table): void {
            $table->decimal('credits_per_second', 10, 4)->nullable()->after('cost_per_second_usd');
        });

        $creditUnitValue = DB::table('settings')
            ->where('key', 'credit_unit_value_usd')
            ->value('value');

        $creditUnitValue = is_numeric($creditUnitValue) && (float) $creditUnitValue > 0
            ? (float) $creditUnitValue
            : 0.35;

        DB::table('models')
            ->whereNotNull('cost_per_second_usd')
            ->orderBy('id')
            ->get(['id', 'cost_per_second_usd'])
            ->each(function (object $model) use ($creditUnitValue): void {
                $costPerSecond = (float) $model->cost_per_second_usd;
                if ($costPerSecond <= 0) {
                    return;
                }

                DB::table('models')
                    ->where('id', $model->id)
                    ->update([
                        'credits_per_second' => number_format($costPerSecond / $creditUnitValue, 4, '.', ''),
                    ]);
            });

        DB::table('models')
            ->whereNull('cost_per_second_usd')
            ->orWhereNull('credits_per_second')
            ->update([
                'active' => false,
                'public_visible' => false,
            ]);
    }

    public function down(): void
    {
        Schema::table('models', function (Blueprint $table): void {
            $table->dropColumn('credits_per_second');
        });
    }
};
