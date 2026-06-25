<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_user_can_register(): void
    {
        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->postJson('/api/auth/register', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'citoyen',
            'type_piece_identite' => 'cnib',
            'piece_identite' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token', 'message'])
            ->assertJsonPath('user.is_active', false)
            ->assertJsonPath('user.role.nom', 'citoyen')
            ->assertJsonPath('user.type_piece_identite', 'cnib');
    }

    public function test_user_cannot_login_before_approval(): void
    {
        $role = Role::where('nom', 'citoyen')->first();
        User::factory()->create([
            'email' => 'jean@test.com',
            'password_hash' => bcrypt('password123'),
            'role_id' => $role->id,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jean@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('code', 'ACCOUNT_PENDING_APPROVAL');
    }

    public function test_admin_can_approve_user(): void
    {
        $roleCitoyen = Role::where('nom', 'citoyen')->first();
        $roleAdmin = Role::where('nom', 'admin')->first();

        $admin = User::factory()->create([
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role_id' => $roleCitoyen->id,
            'is_active' => false,
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->patchJson("/api/admin/users/{$user->id}/approve");

        $response->assertOk()
            ->assertJsonPath('user.is_active', true);

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_user_can_login_after_approval(): void
    {
        $role = Role::where('nom', 'citoyen')->first();
        $user = User::factory()->create([
            'email' => 'jean@test.com',
            'password_hash' => bcrypt('password123'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jean@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user', 'token'])
            ->assertJsonPath('user.is_active', true);
    }

    public function test_role_middleware_blocks_unauthorized(): void
    {
        $roleAdmin = Role::where('nom', 'admin')->first();
        $roleCitoyen = Role::where('nom', 'citoyen')->first();

        User::factory()->create([
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);

        $citoyen = User::factory()->create([
            'role_id' => $roleCitoyen->id,
            'is_active' => true,
        ]);

        $token = $citoyen->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }
}
