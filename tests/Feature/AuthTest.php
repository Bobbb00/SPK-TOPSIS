<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@spk.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@spk.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@spk.test',
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => 'admin@spk.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_users_list_and_create_panitia(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(200);

        $createResponse = $this->actingAs($user)->post('/users', [
            'name' => 'Panitia Baru',
            'email' => 'baru@spk.test',
            'password' => 'password123',
        ]);

        $createResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'baru@spk.test',
        ]);
    }

    public function test_panitia_cannot_delete_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete("/users/{$user->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }
}
