<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Criteria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriteriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_criteria_endpoints(): void
    {
        $response = $this->get('/criteria');
        $response->assertRedirect('/login');

        $storeResponse = $this->post('/criteria', [
            'code' => 'C1',
            'name' => 'IPK',
            'type' => 'benefit',
            'weight' => 0.25,
        ]);
        $storeResponse->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_criteria_list(): void
    {
        $user = User::factory()->create();
        Criteria::create([
            'code' => 'C1',
            'name' => 'IPK Mahasiswa',
            'type' => 'benefit',
            'weight' => 0.35,
        ]);

        $response = $this->actingAs($user)->get('/criteria');

        $response->assertStatus(200);
        $response->assertSee('C1');
        $response->assertSee('IPK Mahasiswa');
    }

    public function test_authenticated_user_can_create_criteria(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/criteria', [
            'code' => 'C1',
            'name' => 'Prestasi Akademik',
            'type' => 'benefit',
            'weight' => 0.40,
        ]);

        $response->assertRedirect('/criteria');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('criterias', [
            'code' => 'C1',
            'name' => 'Prestasi Akademik',
            'type' => 'benefit',
            'weight' => 0.40,
        ]);
    }

    public function test_cannot_create_criteria_with_duplicate_code(): void
    {
        $user = User::factory()->create();
        Criteria::create([
            'code' => 'C1',
            'name' => 'Kriteria 1',
            'type' => 'benefit',
            'weight' => 0.20,
        ]);

        $response = $this->actingAs($user)->post('/criteria', [
            'code' => 'C1',
            'name' => 'Kriteria Duplikat',
            'type' => 'cost',
            'weight' => 0.30,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_cannot_create_criteria_with_invalid_type_or_weight(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/criteria', [
            'code' => 'C2',
            'name' => 'Kriteria Invalid',
            'type' => 'other',
            'weight' => 1.50,
        ]);

        $response->assertSessionHasErrors(['type', 'weight']);
    }

    public function test_authenticated_user_can_update_criteria(): void
    {
        $user = User::factory()->create();
        $criteria = Criteria::create([
            'code' => 'C1',
            'name' => 'Nama Lama',
            'type' => 'benefit',
            'weight' => 0.20,
        ]);

        $response = $this->actingAs($user)->put("/criteria/{$criteria->id}", [
            'code' => 'C1',
            'name' => 'Nama Baru',
            'type' => 'cost',
            'weight' => 0.25,
        ]);

        $response->assertRedirect('/criteria');
        $this->assertDatabaseHas('criterias', [
            'id' => $criteria->id,
            'name' => 'Nama Baru',
            'type' => 'cost',
            'weight' => 0.25,
        ]);
    }

    public function test_authenticated_user_can_delete_criteria(): void
    {
        $user = User::factory()->create();
        $criteria = Criteria::create([
            'code' => 'C1',
            'name' => 'Kriteria Hapus',
            'type' => 'benefit',
            'weight' => 0.20,
        ]);

        $response = $this->actingAs($user)->delete("/criteria/{$criteria->id}");

        $response->assertRedirect('/criteria');
        $this->assertDatabaseMissing('criterias', [
            'id' => $criteria->id,
        ]);
    }

    public function test_authenticated_user_can_batch_update_weights(): void
    {
        $user = User::factory()->create();
        $c1 = Criteria::create(['code' => 'C1', 'name' => 'K1', 'type' => 'benefit', 'weight' => 0.50]);
        $c2 = Criteria::create(['code' => 'C2', 'name' => 'K2', 'type' => 'cost', 'weight' => 0.50]);

        $response = $this->actingAs($user)->post('/criteria/update-weights', [
            'weights' => [
                ['id' => $c1->id, 'weight' => 0.70],
                ['id' => $c2->id, 'weight' => 0.30],
            ],
        ]);

        $response->assertRedirect('/criteria');
        $this->assertDatabaseHas('criterias', ['id' => $c1->id, 'weight' => 0.70]);
        $this->assertDatabaseHas('criterias', ['id' => $c2->id, 'weight' => 0.30]);
    }
}
