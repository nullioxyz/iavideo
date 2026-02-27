<?php

namespace App\Domain\Analytics\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Contacts\Models\Contact;
use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MvpKpisEndpointTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_admin_can_access_mvp_kpis_endpoint(): void
    {
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $admin = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'status' => Input::DONE,
        ]);

        Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'status' => Prediction::SUCCEEDED,
            'created_at' => now()->subMinutes(2),
            'finished_at' => now(),
            'total_ms' => 1000,
        ]);

        CreditLedger::query()->create([
            'user_id' => $user->getKey(),
            'delta' => -1,
            'balance_after' => 2,
            'reason' => 'Charge for input creation',
            'reference_type' => 'input_creation',
            'reference_id' => $input->getKey(),
            'created_at' => now(),
        ]);

        Contact::query()->create([
            'name' => 'Lead',
            'email' => 'lead@example.com',
            'phone' => null,
            'message' => 'Help',
            'is_user' => false,
        ]);

        $token = $this->loginAndGetToken($admin);

        $response = $this->withJwt($token)->getJson('/api/analytics/mvp-kpis');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'activation_rate_percent',
                'avg_time_to_ready_seconds',
                'prediction_failure_rate_percent',
                'retry_success_rate_percent',
                'credits_per_completed_video',
                'refund_rate_percent',
                'contacts_volume_last_30d',
            ],
        ]);
    }

    public function test_non_admin_cannot_access_mvp_kpis_endpoint(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $this->withJwt($token)
            ->getJson('/api/analytics/mvp-kpis')
            ->assertForbidden();
    }
}
