<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ApplicantSeeder;
use Database\Seeders\CriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_dashboard_with_all_metrics(): void
    {
        $this->seed(CriteriaSeeder::class);
        $this->seed(ApplicantSeeder::class);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('top_rankings')
            ->has('criterias')
            ->where('stats.criteria_count', 5)
            ->where('stats.applicant_count', 8)
        );
    }
}
