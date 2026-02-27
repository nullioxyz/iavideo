<?php

namespace App\Filament\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIModels\Models\PresetTag;
use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use App\Filament\Resources\Admins\AdminResource;
use App\Filament\Resources\PresetTags\PresetTagsResource;
use App\Filament\Resources\Predictions\Tables\PredictionsTable;
use App\Filament\Resources\Presets\PresetsResource;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Inputs\Tables\InputsTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FilamentFiltersAndRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([RoleNames::ADMIN, RoleNames::DEV, RoleNames::PLATFORM_USER] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
        }
    }

    public function test_admin_role_filter_returns_only_users_with_selected_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleNames::ADMIN);

        $dev = User::factory()->create();
        $dev->assignRole(RoleNames::DEV);

        $platform = User::factory()->create();
        $platform->assignRole(RoleNames::PLATFORM_USER);

        $query = UsersTable::applyRoleFilter(Admin::query(), RoleNames::ADMIN);

        $this->assertSame([$admin->getKey()], $query->pluck('id')->all());
    }

    public function test_inputs_preset_filter_returns_only_inputs_for_selected_preset(): void
    {
        $model = AIModel::factory()->create(['active' => true]);
        $presetA = Preset::factory()->create(['default_model_id' => $model->getKey(), 'active' => true]);
        $presetB = Preset::factory()->create(['default_model_id' => $model->getKey(), 'active' => true]);

        $inputA = Input::factory()->create(['preset_id' => $presetA->getKey()]);
        Input::factory()->create(['preset_id' => $presetB->getKey()]);

        $query = InputsTable::applyPresetFilter(Input::query(), $presetA->getKey());

        $this->assertSame([$inputA->getKey()], $query->pluck('id')->all());
    }

    public function test_predictions_filters_by_status_name_and_date_range(): void
    {
        $model = AIModel::factory()->create(['active' => true, 'name' => 'Kling Model']);
        $preset = Preset::factory()->create(['default_model_id' => $model->getKey(), 'active' => true]);
        $user = User::factory()->create();

        $matchInput = Input::factory()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => 'Tattoo Dragon',
        ]);

        $outsideInput = Input::factory()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => 'Simple Flower',
        ]);

        $inRangeDate = Carbon::parse('2026-02-20 12:00:00');

        $matchPrediction = Prediction::factory()->create([
            'input_id' => $matchInput->getKey(),
            'model_id' => $model->getKey(),
            'status' => Prediction::FAILED,
            'created_at' => $inRangeDate,
            'failed_at' => $inRangeDate,
        ]);

        Prediction::factory()->create([
            'input_id' => $outsideInput->getKey(),
            'model_id' => $model->getKey(),
            'status' => Prediction::SUCCEEDED,
            'created_at' => Carbon::parse('2026-01-01 12:00:00'),
            'failed_at' => null,
        ]);

        $query = Prediction::query()->where('status', Prediction::FAILED);
        $query = PredictionsTable::applyNameFilter($query, 'Dragon');
        $query = PredictionsTable::applyDateRangeFilter($query, 'created_at', [
            'from' => '2026-02-01 00:00:00',
            'until' => '2026-02-28 23:59:59',
        ]);

        $this->assertSame([$matchPrediction->getKey()], $query->pluck('id')->all());
    }

    public function test_resources_register_required_relation_managers(): void
    {
        $this->assertNotEmpty(UserResource::getRelations());
        $this->assertNotEmpty(PresetsResource::getRelations());
        $this->assertNotEmpty(PresetTagsResource::getRelations());
    }

    public function test_users_and_admins_resources_are_role_scoped(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleNames::ADMIN);

        $platform = User::factory()->create();
        $platform->assignRole(RoleNames::PLATFORM_USER);

        $adminIds = AdminResource::getEloquentQuery()->pluck('id')->all();
        $platformIds = UserResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($admin->getKey(), $adminIds);
        $this->assertNotContains($platform->getKey(), $adminIds);

        $this->assertContains($platform->getKey(), $platformIds);
        $this->assertNotContains($admin->getKey(), $platformIds);
    }

    public function test_preset_tag_relates_to_presets(): void
    {
        $model = AIModel::factory()->create(['active' => true]);
        $preset = Preset::factory()->create(['default_model_id' => $model->getKey(), 'active' => true]);
        $tag = PresetTag::factory()->create(['active' => true]);

        $tag->presets()->attach($preset->getKey());

        $this->assertSame([$preset->getKey()], $tag->presets()->pluck('presets.id')->all());
    }
}
