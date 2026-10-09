<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_applicants_or_evaluations(): void
    {
        $this->get('/applicants')->assertRedirect('/login');
        $this->get('/evaluations')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_applicants_list(): void
    {
        $user = User::factory()->create();
        Applicant::create([
            'nim' => '2024099',
            'name' => 'Siti Nurhaliza',
            'study_program' => 'Sistem Informasi',
        ]);

        $response = $this->actingAs($user)->get('/applicants');

        $response->assertStatus(200);
        $response->assertSee('2024099');
        $response->assertSee('Siti Nurhaliza');
    }

    public function test_authenticated_user_can_create_applicant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/applicants', [
            'nim' => '2024100',
            'name' => 'Rian Hidayat',
            'study_program' => 'Teknik Informatika',
        ]);

        $response->assertRedirect('/applicants');
        $this->assertDatabaseHas('applicants', [
            'nim' => '2024100',
            'name' => 'Rian Hidayat',
        ]);
    }

    public function test_cannot_create_applicant_with_duplicate_nim(): void
    {
        $user = User::factory()->create();
        Applicant::create([
            'nim' => '2024100',
            'name' => 'Rian Hidayat',
            'study_program' => 'Teknik Informatika',
        ]);

        $response = $this->actingAs($user)->post('/applicants', [
            'nim' => '2024100',
            'name' => 'Rian Lain',
            'study_program' => 'Sistem Informasi',
        ]);

        $response->assertSessionHasErrors(['nim']);
    }

    public function test_authenticated_user_can_update_applicant(): void
    {
        $user = User::factory()->create();
        $applicant = Applicant::create([
            'nim' => '2024101',
            'name' => 'Nama Lama',
            'study_program' => 'Teknik Komputer',
        ]);

        $response = $this->actingAs($user)->put("/applicants/{$applicant->id}", [
            'nim' => '2024101',
            'name' => 'Nama Baru',
            'study_program' => 'Teknik Informatika',
        ]);

        $response->assertRedirect('/applicants');
        $this->assertDatabaseHas('applicants', [
            'id' => $applicant->id,
            'name' => 'Nama Baru',
            'study_program' => 'Teknik Informatika',
        ]);
    }

    public function test_authenticated_user_can_delete_applicant(): void
    {
        $user = User::factory()->create();
        $applicant = Applicant::create([
            'nim' => '2024102',
            'name' => 'Hapus Mahasiswa',
            'study_program' => 'Teknik Informatika',
        ]);

        $response = $this->actingAs($user)->delete("/applicants/{$applicant->id}");

        $response->assertRedirect('/applicants');
        $this->assertDatabaseMissing('applicants', [
            'id' => $applicant->id,
        ]);
    }

    public function test_authenticated_user_can_view_evaluations_matrix(): void
    {
        $user = User::factory()->create();
        $c = Criteria::create(['code' => 'C1', 'name' => 'IPK', 'type' => 'benefit', 'weight' => 1.0]);
        $a = Applicant::create(['nim' => '2024103', 'name' => 'Test Eval', 'study_program' => 'TI']);
        Evaluation::create(['applicant_id' => $a->id, 'criteria_id' => $c->id, 'score' => 3.75]);

        $response = $this->actingAs($user)->get('/evaluations');

        $response->assertStatus(200);
        $response->assertSee('Test Eval');
    }

    public function test_authenticated_user_can_update_evaluation_scores(): void
    {
        $user = User::factory()->create();
        $c1 = Criteria::create(['code' => 'C1', 'name' => 'IPK', 'type' => 'benefit', 'weight' => 0.6]);
        $c2 = Criteria::create(['code' => 'C2', 'name' => 'Gaji', 'type' => 'cost', 'weight' => 0.4]);
        $a = Applicant::create(['nim' => '2024104', 'name' => 'Nilai Update', 'study_program' => 'SI']);

        $response = $this->actingAs($user)->put("/evaluations/{$a->id}", [
            'scores' => [
                ['criteria_id' => $c1->id, 'score' => 3.85],
                ['criteria_id' => $c2->id, 'score' => 2500000],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('evaluations', [
            'applicant_id' => $a->id,
            'criteria_id' => $c1->id,
            'score' => 3.85,
        ]);
        $this->assertDatabaseHas('evaluations', [
            'applicant_id' => $a->id,
            'criteria_id' => $c2->id,
            'score' => 2500000,
        ]);
    }
}
