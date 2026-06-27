<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\SeatPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CoachApiTest extends TestCase
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
    public function test_authenticated_user_can_list_coaches(): void
    {
        Coach::factory()->count(3)->create();

        $response = $this->getJson('/api/coaches', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'coach_no', 'seat_plan_id', 'coach_type', 'status']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_coaches(): void
    {
        $this->getJson('/api/coaches')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_index_supports_search(): void
    {
        Coach::factory()->create(['coach_no' => 'AB-001']);
        Coach::factory()->create(['coach_no' => 'CD-002']);

        $response = $this->getJson('/api/coaches?search=AB-001', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_coach(): void
    {
        $seatPlan = SeatPlan::factory()->create();

        $payload = [
            'coach_no' => 'XY-999',
            'seat_plan_id' => $seatPlan->id,
            'coach_type' => 1,
        ];

        $response = $this->postJson('/api/coaches', $payload, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201);

        $this->assertDatabaseHas('coaches', ['coach_no' => 'XY-999', 'seat_plan_id' => $seatPlan->id]);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/coaches', [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_duplicate_coach_no(): void
    {
        $seatPlan = SeatPlan::factory()->create();
        Coach::factory()->create(['coach_no' => 'DUP-001']);

        $response = $this->postJson('/api/coaches', [
            'coach_no' => 'DUP-001',
            'seat_plan_id' => $seatPlan->id,
            'coach_type' => 1,
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_store_fails_with_invalid_coach_type(): void
    {
        $seatPlan = SeatPlan::factory()->create();

        $response = $this->postJson('/api/coaches', [
            'coach_no' => 'VL-001',
            'seat_plan_id' => $seatPlan->id,
            'coach_type' => 99,
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_store_fails_with_nonexistent_seat_plan(): void
    {
        $response = $this->postJson('/api/coaches', [
            'coach_no' => 'VL-002',
            'seat_plan_id' => 99999,
            'coach_type' => 1,
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_create_coach(): void
    {
        $this->postJson('/api/coaches', ['coach_no' => 'XY-000'])
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_coach(): void
    {
        $coach = Coach::factory()->create();

        $response = $this->getJson("/api/coaches/{$coach->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $coach->id)
            ->assertJsonPath('data.coach_no', $coach->coach_no);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_coach(): void
    {
        $this->getJson('/api/coaches/99999', $this->authHeaders())
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_coach(): void
    {
        $coach = Coach::factory()->create(['coach_no' => 'OLD-001']);
        $seatPlan = SeatPlan::factory()->create();

        $response = $this->putJson("/api/coaches/{$coach->id}", [
            'coach_no' => 'NEW-001',
            'seat_plan_id' => $seatPlan->id,
            'coach_type' => 2,
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.coach_no', 'NEW-001');

        $this->assertDatabaseHas('coaches', ['id' => $coach->id, 'coach_no' => 'NEW-001']);
    }

    /**
     * @test
     */
    public function test_update_allows_same_coach_no_for_same_coach(): void
    {
        $coach = Coach::factory()->create(['coach_no' => 'SAME-001']);

        $response = $this->putJson("/api/coaches/{$coach->id}", [
            'coach_no' => 'SAME-001',
            'seat_plan_id' => $coach->seat_plan_id,
            'coach_type' => $coach->coach_type,
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    /**
     * @test
     */
    public function test_update_fails_with_missing_required_fields(): void
    {
        $coach = Coach::factory()->create();

        $this->putJson("/api/coaches/{$coach->id}", [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_coach(): void
    {
        $seatPlan = SeatPlan::factory()->create();

        $this->putJson('/api/coaches/99999', [
            'coach_no' => 'NA-000',
            'seat_plan_id' => $seatPlan->id,
            'coach_type' => 1,
        ], $this->authHeaders())
            ->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_delete_coach(): void
    {
        $coach = Coach::factory()->create();

        $response = $this->deleteJson("/api/coaches/{$coach->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertSoftDeleted('coaches', ['id' => $coach->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_coach(): void
    {
        $this->deleteJson('/api/coaches/99999', [], $this->authHeaders())
            ->assertStatus(404);
    }
}
