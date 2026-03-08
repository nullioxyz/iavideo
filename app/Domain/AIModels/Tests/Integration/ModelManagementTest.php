<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_management_fields_can_be_created_edited_and_inactivated(): void
    {
        $model = AIModel::factory()->create([
            'name' => 'Initial Name',
            'provider_model_key' => 'provider/model-a',
            'cost_per_second_usd' => '0.0500',
            'credits_per_second' => '0.1500',
            'active' => true,
            'public_visible' => true,
            'sort_order' => 5,
        ]);

        $model->update([
            'name' => 'Updated Name',
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '0.2000',
            'active' => false,
            'public_visible' => false,
            'sort_order' => 10,
        ]);

        $this->assertDatabaseHas('models', [
            'id' => $model->getKey(),
            'name' => 'Updated Name',
            'provider_model_key' => 'provider/model-a',
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '0.2000',
            'active' => false,
            'public_visible' => false,
            'sort_order' => 10,
        ]);
    }
}
