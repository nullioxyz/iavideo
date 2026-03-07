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
            $table->string('provider_model_key')->nullable()->after('slug');
            $table->decimal('cost_per_second_usd', 10, 4)->nullable()->after('version');
            $table->boolean('public_visible')->default(true)->after('active');
            $table->unsignedInteger('sort_order')->default(0)->after('public_visible');
        });

        DB::statement('UPDATE models SET provider_model_key = slug WHERE provider_model_key IS NULL');

        Schema::table('models', function (Blueprint $table): void {
            $table->unique('provider_model_key');
            $table->index(['active', 'public_visible', 'sort_order'], 'models_active_public_sort_idx');
        });

        Schema::table('inputs', function (Blueprint $table): void {
            $table->foreignId('model_id')
                ->nullable()
                ->after('preset_id')
                ->constrained('models')
                ->restrictOnDelete();

            $table->unsignedInteger('duration_seconds')->nullable()->after('size_bytes');
            $table->decimal('estimated_cost_usd', 10, 4)->nullable()->after('duration_seconds');
            $table->unsignedInteger('credits_charged')->default(0)->after('estimated_cost_usd');
            $table->string('billing_status')->default('none')->after('credits_charged');

            $table->index(['model_id', 'created_at'], 'inputs_model_created_at_idx');
            $table->index(['billing_status'], 'inputs_billing_status_idx');
        });

        DB::statement('
            UPDATE inputs
            SET model_id = (
                SELECT presets.default_model_id
                FROM presets
                WHERE presets.id = inputs.preset_id
            )
            WHERE model_id IS NULL
        ');

        DB::statement('
            UPDATE inputs
            SET duration_seconds = (
                SELECT presets.duration_seconds
                FROM presets
                WHERE presets.id = inputs.preset_id
            )
            WHERE duration_seconds IS NULL
        ');

        DB::statement('UPDATE inputs SET credits_charged = 1 WHERE credit_debited = 1 AND credits_charged = 0');
        DB::statement("UPDATE inputs SET billing_status = 'charged' WHERE credit_debited = 1");

        Schema::table('credit_ledger', function (Blueprint $table): void {
            $table->integer('balance_before')->nullable()->after('delta');
            $table->string('operation_type')->nullable()->after('reason');

            $table->foreignId('model_id')
                ->nullable()
                ->after('reference_id')
                ->constrained('models')
                ->nullOnDelete();

            $table->foreignId('preset_id')
                ->nullable()
                ->after('model_id')
                ->constrained('presets')
                ->nullOnDelete();

            $table->unsignedInteger('duration_seconds')->nullable()->after('preset_id');
            $table->decimal('generation_cost_usd', 10, 4)->nullable()->after('duration_seconds');
            $table->string('idempotency_key')->nullable()->after('generation_cost_usd');
            $table->json('metadata')->nullable()->after('idempotency_key');
        });

        DB::statement('UPDATE credit_ledger SET balance_before = balance_after - delta WHERE balance_before IS NULL');

        DB::statement("
            UPDATE credit_ledger
            SET operation_type = CASE
                WHEN reference_type = 'input_creation' THEN 'generation_debit'
                WHEN reference_type IN (
                    'input_prediction_creation_failed',
                    'input_prediction_creation_canceled',
                    'input_video_generation_failed',
                    'input_video_generation_canceled'
                ) THEN 'generation_refund'
                WHEN reference_type = 'credit_purchase' THEN 'credit_purchase'
                WHEN reference_type = 'invite_redemption' THEN 'invite_redemption'
                WHEN delta < 0 THEN 'legacy_debit'
                ELSE 'legacy_credit'
            END
            WHERE operation_type IS NULL
        ");

        Schema::table('credit_ledger', function (Blueprint $table): void {
            $table->unique('idempotency_key');
            $table->index(['user_id', 'operation_type', 'created_at'], 'credit_ledger_user_operation_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('credit_ledger', function (Blueprint $table): void {
            $table->dropIndex('credit_ledger_user_operation_created_idx');
            $table->dropUnique(['idempotency_key']);
            $table->dropConstrainedForeignId('preset_id');
            $table->dropConstrainedForeignId('model_id');
            $table->dropColumn([
                'balance_before',
                'operation_type',
                'duration_seconds',
                'generation_cost_usd',
                'idempotency_key',
                'metadata',
            ]);
        });

        Schema::table('inputs', function (Blueprint $table): void {
            $table->dropIndex('inputs_model_created_at_idx');
            $table->dropIndex('inputs_billing_status_idx');
            $table->dropConstrainedForeignId('model_id');
            $table->dropColumn([
                'duration_seconds',
                'estimated_cost_usd',
                'credits_charged',
                'billing_status',
            ]);
        });

        Schema::table('models', function (Blueprint $table): void {
            $table->dropIndex('models_active_public_sort_idx');
            $table->dropUnique(['provider_model_key']);
            $table->dropColumn([
                'provider_model_key',
                'cost_per_second_usd',
                'public_visible',
                'sort_order',
            ]);
        });
    }
};
