<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\ScholarshipQuota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    protected function setupTestData(): void
    {
        ScholarshipQuota::create(['quota_limit' => 2, 'period' => '2026/2027']);

        $c1 = Criteria::create(['code' => 'C1', 'name' => 'IPK', 'type' => 'benefit', 'weight' => 0.60]);
        $c2 = Criteria::create(['code' => 'C2', 'name' => 'Penghasilan', 'type' => 'cost', 'weight' => 0.40]);

        $a1 = Applicant::create(['nim' => '2024001', 'name' => 'Bagus', 'study_program' => 'TI']);
        $a2 = Applicant::create(['nim' => '2024002', 'name' => 'Bambang', 'study_program' => 'SI']);
        $a3 = Applicant::create(['nim' => '2024003', 'name' => 'Cantika', 'study_program' => 'TI']);

        Evaluation::create(['applicant_id' => $a1->id, 'criteria_id' => $c1->id, 'score' => 3.90]);
        Evaluation::create(['applicant_id' => $a1->id, 'criteria_id' => $c2->id, 'score' => 2000000]);

        Evaluation::create(['applicant_id' => $a2->id, 'criteria_id' => $c1->id, 'score' => 3.50]);
        Evaluation::create(['applicant_id' => $a2->id, 'criteria_id' => $c2->id, 'score' => 5000000]);

        Evaluation::create(['applicant_id' => $a3->id, 'criteria_id' => $c1->id, 'score' => 3.80]);
        Evaluation::create(['applicant_id' => $a3->id, 'criteria_id' => $c2->id, 'score' => 2500000]);
    }

    public function test_guest_is_redirected_from_ranking_endpoints(): void
    {
        $this->get('/ranking')->assertRedirect('/login');
        $this->get('/ranking/export-csv')->assertRedirect('/login');
        $this->get('/ranking/print')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_ranking_and_transparency(): void
    {
        $this->setupTestData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ranking');

        $response->assertStatus(200);
        $response->assertSee('Bagus');
        $response->assertSee('Cantika');
        $response->assertSee('Bambang');
    }

    public function test_ranking_can_be_filtered_by_search_query(): void
    {
        $this->setupTestData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ranking?search=Bagus');

        $response->assertStatus(200);
        $response->assertSee('Bagus');
    }

    public function test_authenticated_user_can_export_csv(): void
    {
        $this->setupTestData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ranking/export-csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_authenticated_user_can_view_print_page(): void
    {
        $this->setupTestData();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ranking/print');

        $response->assertStatus(200);
        $response->assertSee('Bagus');
    }
}
