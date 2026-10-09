<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Akses & Otorisasi
    // -------------------------------------------------------------------------

    public function test_guests_cannot_access_users_endpoints(): void
    {
        $this->get('/users')->assertRedirect('/login');
        $this->post('/users', [])->assertRedirect('/login');
        $this->delete('/users/1')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_users_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/users')->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Tambah Akun Panitia
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_create_new_panitia(): void
    {
        $actor = User::factory()->create();

        $response = $this->actingAs($actor)->post('/users', [
            'name'     => 'Panitia Baru',
            'email'    => 'baru@spk.test',
            'password' => 'rahasiaaman',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'baru@spk.test']);
    }

    public function test_cannot_create_panitia_with_duplicate_email(): void
    {
        $actor    = User::factory()->create();
        $existing = User::factory()->create(['email' => 'duplikat@spk.test']);

        $response = $this->actingAs($actor)->post('/users', [
            'name'     => 'Duplikat',
            'email'    => 'duplikat@spk.test',
            'password' => 'rahasiaaman',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_cannot_create_panitia_with_short_password(): void
    {
        $actor = User::factory()->create();

        $response = $this->actingAs($actor)->post('/users', [
            'name'     => 'Panitia Lemah',
            'email'    => 'lemah@spk.test',
            'password' => 'abc',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_cannot_create_panitia_with_invalid_email(): void
    {
        $actor = User::factory()->create();

        $response = $this->actingAs($actor)->post('/users', [
            'name'     => 'Panitia Invalid',
            'email'    => 'bukan-email',
            'password' => 'rahasiaaman',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    // -------------------------------------------------------------------------
    // Ganti Kata Sandi
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_update_another_users_password(): void
    {
        $actor  = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($actor)->put("/users/{$target->id}/password", [
            'password'              => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_cannot_update_password_without_confirmation(): void
    {
        $actor  = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($actor)->put("/users/{$target->id}/password", [
            'password'              => 'passwordbaru123',
            'password_confirmation' => 'tidakcocok999',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // -------------------------------------------------------------------------
    // Hapus Akun
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_delete_another_panitia(): void
    {
        $actor  = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($actor)->delete("/users/{$target->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_panitia_cannot_delete_their_own_account(): void
    {
        $actor = User::factory()->create();

        $response = $this->actingAs($actor)->delete("/users/{$actor->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $actor->id]);
    }
}
