<?php

namespace Tests\Feature;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DesignationApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
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
    public function test_authenticated_user_can_list_designations(): void
    {
        Designation::factory()->count(3)->create();

        $response = $this->getJson('/api/designations', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'name', 'status']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_designations(): void
    {
        $this->getJson('/api/designations')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_index_supports_search(): void
    {
        Designation::factory()->create(['name' => 'Manager']);
        Designation::factory()->create(['name' => 'Driver']);

        $response = $this->getJson('/api/designations?search=Manager', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ---------------------------------------------------------------
    // ALL ACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_list_all_active_designations(): void
    {
        Designation::factory()->create(['status' => 1]);
        Designation::factory()->create(['status' => 0]);

        $response = $this->getJson('/api/designations/all-active', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'name', 'status']],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_designation(): void
    {
        $payload = [
            'name' => 'Manager',
            'status' => 1,
        ];

        $response = $this->postJson('/api/designations', $payload, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201);

        $this->assertDatabaseHas('designations', ['name' => 'Manager']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/designations', [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_duplicate_name(): void
    {
        Designation::factory()->create(['name' => 'Manager']);

        $response = $this->postJson('/api/designations', ['name' => 'Manager'], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_create_designation(): void
    {
        $this->postJson('/api/designations', ['name' => 'Manager'])
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_designation(): void
    {
        $designation = Designation::factory()->create();

        $response = $this->getJson("/api/designations/{$designation->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $designation->id)
            ->assertJsonPath('data.name', $designation->name);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_designation(): void
    {
        $this->getJson('/api/designations/99999', $this->authHeaders())
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_designation(): void
    {
        $designation = Designation::factory()->create(['name' => 'OldName']);

        $response = $this->putJson("/api/designations/{$designation->id}", [
            'name' => 'NewName',
            'status' => 1,
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'NewName');

        $this->assertDatabaseHas('designations', ['id' => $designation->id, 'name' => 'NewName']);
    }

    /**
     * @test
     */
    public function test_update_fails_with_missing_required_fields(): void
    {
        $designation = Designation::factory()->create();

        $this->putJson("/api/designations/{$designation->id}", [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_designation(): void
    {
        $this->putJson('/api/designations/99999', ['name' => 'NewName'], $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_update_allows_same_name_for_same_designation(): void
    {
        $designation = Designation::factory()->create(['name' => 'Manager']);

        $response = $this->putJson("/api/designations/{$designation->id}", [
            'name' => 'Manager',
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
    public function test_authenticated_user_can_delete_designation(): void
    {
        $designation = Designation::factory()->create();

        $response = $this->deleteJson("/api/designations/{$designation->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertSoftDeleted('designations', ['id' => $designation->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_designation(): void
    {
        $this->deleteJson('/api/designations/99999', [], $this->authHeaders())
            ->assertStatus(404);
    }
}
