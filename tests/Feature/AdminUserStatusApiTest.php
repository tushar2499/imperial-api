<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminUserStatusApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $authUser;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authUser = User::factory()->create([
            'user_name' => 'testadmin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->token = JWTAuth::fromUser($this->authUser);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ---------------------------------------------------------------
    // ACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_activate_admin_user(): void
    {
        $user = User::factory()->create(['status' => 0]);

        $response = $this->patchJson("/api/admin-users/{$user->id}/active", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Admin user activated successfully')
            ->assertJsonPath('data.status', 1);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 1]);
    }

    /**
     * @test
     */
    public function test_activate_already_active_user_stays_active(): void
    {
        $user = User::factory()->create(['status' => 1]);

        $response = $this->patchJson("/api/admin-users/{$user->id}/active", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 1);
    }

    /**
     * @test
     */
    public function test_activate_returns_404_for_nonexistent_user(): void
    {
        $this->patchJson('/api/admin-users/99999/active', [], $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_activate_requires_authentication(): void
    {
        $user = User::factory()->create(['status' => 1]);

        $this->patchJson("/api/admin-users/{$user->id}/active")
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // INACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_deactivate_admin_user(): void
    {
        $user = User::factory()->create(['status' => 1]);

        $response = $this->patchJson("/api/admin-users/{$user->id}/inactive", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Admin user deactivated successfully')
            ->assertJsonPath('data.status', 0);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 0]);
    }

    /**
     * @test
     */
    public function test_deactivate_already_inactive_user_stays_inactive(): void
    {
        $user = User::factory()->create(['status' => 0]);

        $response = $this->patchJson("/api/admin-users/{$user->id}/inactive", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 0);
    }

    /**
     * @test
     */
    public function test_deactivate_returns_404_for_nonexistent_user(): void
    {
        $this->patchJson('/api/admin-users/99999/inactive', [], $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_deactivate_requires_authentication(): void
    {
        $user = User::factory()->create(['status' => 1]);

        $this->patchJson("/api/admin-users/{$user->id}/inactive")
            ->assertStatus(401);
    }
}
