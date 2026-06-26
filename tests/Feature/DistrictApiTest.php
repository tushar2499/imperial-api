<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DistrictApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_list_districts(): void
    {
        District::factory()->count(3)->create();

        $response = $this->getJson('/api/districts', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'name', 'code', 'status']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_districts(): void
    {
        $this->getJson('/api/districts')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_index_supports_search(): void
    {
        District::factory()->create(['name' => 'Dhaka', 'code' => 'DHK']);
        District::factory()->create(['name' => 'Chittagong', 'code' => 'CTG']);

        $response = $this->getJson('/api/districts?search=Dhaka', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ---------------------------------------------------------------
    // ALL ACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_list_all_active_districts(): void
    {
        District::factory()->create(['status' => 1]);
        District::factory()->create(['status' => 0]);

        $response = $this->getJson('/api/districts/all-active', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'name', 'code', 'status']],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_district(): void
    {
        $payload = [
            'name' => 'Dhaka',
            'code' => 'DHK',
        ];

        $response = $this->postJson('/api/districts', $payload, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201);

        $this->assertDatabaseHas('districts', ['name' => 'Dhaka', 'code' => 'DHK']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/districts', [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_duplicate_name(): void
    {
        District::factory()->create(['name' => 'Dhaka']);

        $response = $this->postJson('/api/districts', ['name' => 'Dhaka'], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_create_district(): void
    {
        $this->postJson('/api/districts', ['name' => 'Dhaka'])
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_district(): void
    {
        $district = District::factory()->create();

        $response = $this->getJson("/api/districts/{$district->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $district->id)
            ->assertJsonPath('data.name', $district->name);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_district(): void
    {
        $this->getJson('/api/districts/99999', $this->authHeaders())
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_district(): void
    {
        $district = District::factory()->create(['name' => 'OldName']);

        $response = $this->putJson("/api/districts/{$district->id}", [
            'name' => 'NewName',
            'code' => 'NEW',
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'NewName');

        $this->assertDatabaseHas('districts', ['id' => $district->id, 'name' => 'NewName']);
    }

    /**
     * @test
     */
    public function test_update_fails_with_missing_required_fields(): void
    {
        $district = District::factory()->create();

        $this->putJson("/api/districts/{$district->id}", [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_district(): void
    {
        $this->putJson('/api/districts/99999', ['name' => 'Anywhere'], $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_update_allows_same_name_for_same_district(): void
    {
        $district = District::factory()->create(['name' => 'Dhaka']);

        $response = $this->putJson("/api/districts/{$district->id}", [
            'name' => 'Dhaka',
            'code' => 'DHK',
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_delete_district(): void
    {
        $district = District::factory()->create();

        $response = $this->deleteJson("/api/districts/{$district->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertSoftDeleted('districts', ['id' => $district->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_district(): void
    {
        $this->deleteJson('/api/districts/99999', [], $this->authHeaders())
            ->assertStatus(404);
    }
}
