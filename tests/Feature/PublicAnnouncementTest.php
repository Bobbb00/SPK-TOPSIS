<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\ScholarshipQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ScholarshipQuota::create([
            'quota_limit' => 2,
            'period' => '2026/2027',
        ]);

        $c1 = Criteria::create([
            'code' => 'C1',
            'name' => 'IPK',
            'type' => 'benefit',
            'weight' => 0.6,
        ]);
        $c2 = Criteria::create([
            'code' => 'C2',
            'name' => 'Penghasilan',
            'type' => 'cost',
            'weight' => 0.4,
        ]);

        $app1 = Applicant::create([
            'nim' => '2024001',
            'name' => 'Citra Dewi',
            'study_program' => 'Teknik Informatika',
        ]);
        Evaluation::create(['applicant_id' => $app1->id, 'criteria_id' => $c1->id, 'score' => 3.9]);
        Evaluation::create(['applicant_id' => $app1->id, 'criteria_id' => $c2->id, 'score' => 1500000]);

        $app2 = Applicant::create([
            'nim' => '2024002',
            'name' => 'Budi Santoso',
            'study_program' => 'Sistem Informasi',
        ]);
        Evaluation::create(['applicant_id' => $app2->id, 'criteria_id' => $c1->id, 'score' => 3.0]);
        Evaluation::create(['applicant_id' => $app2->id, 'criteria_id' => $c2->id, 'score' => 4500000]);
    }

    public function test_guest_can_view_public_announcement_page(): void
    {
        $response = $this->get('/pengumuman');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Announcement')
            ->has('quota')
        );
    }

    public function test_student_can_check_announcement_by_valid_nim(): void
    {
        $response = $this->postJson('/pengumuman/check', [
            'nim' => '2024001',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.applicant.nim', '2024001');
        $response->assertJsonPath('data.applicant.name', 'Citra Dewi');
        $response->assertJsonPath('data.applicant.is_passed', true);
        $response->assertJsonStructure([
            'data' => [
                'applicant' => ['id', 'nim', 'name', 'study_program', 'rank', 'score', 'is_passed'],
                'quota' => ['quota_limit', 'period'],
                'key_factors',
                'explanation',
                'engine',
            ],
        ]);
    }

    public function test_student_gets_404_when_nim_not_found(): void
    {
        $response = $this->postJson('/pengumuman/check', [
            'nim' => '9999999',
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_student_gets_422_when_name_verification_mismatches(): void
    {
        $response = $this->postJson('/pengumuman/check', [
            'nim' => '2024001',
            'name' => 'Salah Orang',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }
}
