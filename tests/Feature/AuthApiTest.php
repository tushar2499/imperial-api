<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthApiTest extends TestCase
{
    use DatabaseTransactions;

    // ---------------------------------------------------------------
    // REGISTER
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'user_name' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'user_name'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', ['user_name' => 'newuser']);
    }

    /**
     * @test
     */
    public function test_register_fails_with_missing_required_fields(): void
    {
        $this->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_register_fails_with_mismatched_password_confirmation(): void
    {
        $this->postJson('/api/register', [
            'user_name' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_register_fails_with_password_too_short(): void
    {
        $this->postJson('/api/register', [
            'user_name' => 'newuser',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_register_fails_when_username_is_already_taken(): void
    {
        User::factory()->create(['user_name' => 'existinguser']);

        $this->postJson('/api/register', [
            'user_name' => 'existinguser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // LOGIN
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'user_name' => 'testuser',
            'password' => bcrypt('password123'),
            'status' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'user_name' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'user_name'],
                    'token',
                ],
            ]);
    }

    /**
     * @test
     */
    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'user_name' => 'testuser',
            'password' => bcrypt('correctpassword'),
            'status' => 1,
        ]);

        $this->postJson('/api/login', [
            'user_name' => 'testuser',
            'password' => 'wrongpassword',
        ])->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $this->postJson('/api/login', [
            'user_name' => 'nobody',
            'password' => 'password123',
        ])->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_login_fails_with_missing_fields(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'user_name' => 'inactiveuser',
            'password' => bcrypt('password123'),
            'status' => 0,
        ]);

        $this->postJson('/api/login', [
            'user_name' => 'inactiveuser',
            'password' => 'password123',
        ])->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // REFRESH TOKEN
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_refresh_token(): void
    {
        $user = User::factory()->create(['status' => 1]);
        $token = JWTAuth::fromUser($user);

        $this->postJson('/api/refresh-token', [], ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['token']]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_refresh_token(): void
    {
        $this->postJson('/api/refresh-token')
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // LOGOUT
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['status' => 1]);
        $token = JWTAuth::fromUser($user);

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $this->postJson('/api/logout')
            ->assertStatus(401);
    }
}
