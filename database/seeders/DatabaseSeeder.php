<?php

namespace Database\Seeders;

use App\Domain\AIModels\Models\Model;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Platforms\Models\Platform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create(
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'username' => 'test.user',
                'active' => true,
                'credit_balance' => 3,
            ],
        );

        User::factory()->create(
            [
                'name' => 'admin',
                'email' => 'admin@ai.com',
                'password' => bcrypt('password'),
                'username' => 'admin',
                'active' => true,
                'credit_balance' => 3,
            ],
        );

        // platform
        $platform = Platform::factory()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
        ]);

        $model = Model::factory()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'version' => '2.5',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Preset::factory()->create([
            'name' => 'Warm',
            'prompt' => 'Realistic human arm with a tattoo applied on the skin.
                The arm performs a slow, natural movement, with subtle muscle motion.
                Preserve tattoo linework, do not distort or warp the tattoo.
                Cinematic lighting, realistic skin texture, high detail.
                The tattoo remains perfectly aligned with the skin during movement.',
            'negative_prompt' => 'distorted tattoo, warped lines, unrealistic motion, extra limbs, blurry tattoo, low quality',
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Preset::factory()->create([
            'name' => 'Forearm',
            'prompt' => 'Realistic human forearm with tattoo.
                The forearm slowly rotates and flexes naturally.
                Subtle skin and muscle deformation, preserving tattoo integrity.
                Tattoo remains sharp, crisp, and follows the skin naturally.
                High realism, cinematic style.',
            'negative_prompt' => 'distorted tattoo, warped lines, unrealistic motion, extra limbs, blurry tattoo, low quality',
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Preset::factory()->create([
            'name' => 'Back',
            'prompt' => 'Human back with a tattoo.
                Natural breathing motion causes subtle movement of the skin.
                Tattoo remains stable and undistorted.
                Realistic anatomy, cinematic lighting, professional realism.
            ',
            'negative_prompt' => 'distorted tattoo, warped lines, unrealistic motion, extra limbs, blurry tattoo, low quality',
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Preset::factory()->create([
            'name' => 'Male chest',
            'prompt' => 'Male human chest with tattoo.
                Subtle breathing motion.
                No nipples visible.
                Preserve tattoo design and linework.
                Realistic skin texture, cinematic realism.
            ',
            'negative_prompt' => 'distorted tattoo, warped lines, unrealistic motion, extra limbs, blurry tattoo, low quality',
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Preset::factory()->create([
            'name' => 'Female chest',
            'prompt' => 'Female torso with tattoo.
                Chest area covered or smooth, no nipples visible.
                Subtle breathing motion.
                Preserve tattoo integrity and artistic lines.
                High realism, cinematic lighting.
            ',
            'negative_prompt' => 'distorted tattoo, warped lines, unrealistic motion, extra limbs, blurry tattoo, low quality',
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Preset::factory()->create([
            'name' => 'Region close to the intimate',
            'prompt' => 'Human lower torso with tattoo near the hip area.
                The intimate regions are fully covered, cropped, or softly blurred.
                Natural body movement, subtle motion.
                Tattoo remains clear and undistorted.
                Professional, artistic, non-sexual presentation.
            ',
            'negative_prompt' => 'nudity, genitals, explicit anatomy, sexualized content',
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
