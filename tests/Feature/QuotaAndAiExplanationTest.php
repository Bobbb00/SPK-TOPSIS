<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ScholarshipQuota;
use App\Models\User;
use Database\Seeders\ApplicantSeeder;
use Database\Seeders\CriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotaAndAiExplanationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_scholarship_quota(): void
    {
        $user = User::factory()->create();
        ScholarshipQuota::create(['quota_limit' => 5, 'period' => '2025/2026']);

        $response = $this->actingAs($user)->put('/scholarship-quota', [
            'quota_limit' => 12,
            'period' => 'Semester Ganjil 2026/2027',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('scholarship_quotas', [
            'quota_limit' => 12,
            'period' => 'Semester Ganjil 2026/2027',
        ]);
    }

    public function test_cannot_update_quota_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/scholarship-quota', [
            'quota_limit' => 0, // min 1
            'period' => '',
        ]);

        $response->assertSessionHasErrors(['quota_limit', 'period']);
    }

    public function test_authenticated_user_can_get_ai_explanation_for_applicant(): void
    {
        $this->seed(CriteriaSeeder::class);
        $this->seed(ApplicantSeeder::class);

        $user = User::factory()->create();
        ScholarshipQuota::create(['quota_limit' => 3, 'period' => '2025/2026']);

        $applicant = Applicant::firstOrFail();

        $response = $this->actingAs($user)->get("/ranking/{$applicant->id}/explain");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'applicant' => ['id', 'nim', 'name', 'study_program', 'rank', 'score', 'is_passed'],
            'quota_limit',
            'explanation',
            'key_factors',
            'strengths',
            'engine',
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['explanation']);
        $this->assertIsArray($json['key_factors']);
    }

    public function test_ai_explanation_is_cached_and_flushed_on_quota_or_eval_update(): void
    {
        $this->seed(CriteriaSeeder::class);
        $this->seed(ApplicantSeeder::class);

        $user = User::factory()->create();
        $quota = ScholarshipQuota::create(['quota_limit' => 3, 'period' => '2025/2026']);
        $applicant = Applicant::firstOrFail();

        $cacheKey = "ai_explanation:applicant_{$applicant->id}";
        $cache = \Illuminate\Support\Facades\Cache::supportsTags()
            ? \Illuminate\Support\Facades\Cache::tags(['ai_explanations'])
            : \Illuminate\Support\Facades\Cache::store();

        // Belum ada di cache
        $this->assertNull($cache->get($cacheKey));

        // Panggil endpoint explanation
        $this->actingAs($user)->getJson("/ranking/{$applicant->id}/explain")->assertStatus(200);

        // Sekarang tersimpan di cache
        $this->assertNotNull($cache->get($cacheKey));
        $cachedData = $cache->get($cacheKey);
        $this->assertEquals($applicant->id, $cachedData['applicant']['id']);

        // Jika kuota diperbarui, cache harus otomatis ter-flush
        $this->actingAs($user)->put('/scholarship-quota', [
            'quota_limit' => 10,
            'period' => '2025/2026 Revisi',
        ])->assertSessionHas('success');

        $this->assertNull($cache->get($cacheKey));

        // Generate ulang
        $this->actingAs($user)->getJson("/ranking/{$applicant->id}/explain")->assertStatus(200);
        $this->assertNotNull($cache->get($cacheKey));

        // Jika evaluasi nilai diubah, cache harus ter-flush lagi
        $criteria = \App\Models\Criteria::firstOrFail();
        $this->actingAs($user)->put("/evaluations/{$applicant->id}", [
            'scores' => [
                ['criteria_id' => $criteria->id, 'score' => 4.0],
            ],
        ])->assertSessionHas('success');

        $this->assertNull($cache->get($cacheKey));
    }
}
