<?php

namespace Tests\Feature;

use App\Models\SeatPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SeatPlanApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private string $token;

    /** @var array<string, mixed> */
    private array $validPayload;

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

        $this->validPayload = [
            'name' => 'Test Seat Plan',
            'floor' => 1,
            'status' => 1,
            'floors_data' => [
                [
                    'name' => 'Lower Deck',
                    'layoutType' => '2+2',
                    'rows' => 3,
                    'cols' => 4,
                    'step' => 4,
                    'extraSeat' => false,
                    'seats' => [
                        ['rowNumber' => 1, 'colNumber' => 1, 'seatName' => 'A1', 'seatType' => 'Economy', 'isDisable' => 0, 'status' => 1],
                        ['rowNumber' => 1, 'colNumber' => 2, 'seatName' => 'A2', 'seatType' => 'Economy', 'isDisable' => 0, 'status' => 1],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, string> */
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
    public function test_authenticated_user_can_list_seat_plans(): void
    {
        SeatPlan::factory()->count(3)->create();

        $response = $this->getJson('/api/seat-plans', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'name', 'floor', 'status']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_seat_plans(): void
    {
        $this->getJson('/api/seat-plans')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_index_supports_search_filter(): void
    {
        SeatPlan::factory()->create(['name' => 'VIP Plan']);
        SeatPlan::factory()->create(['name' => 'Economy Plan']);

        $response = $this->getJson('/api/seat-plans?search=VIP', $this->authHeaders());

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('VIP Plan', $data[0]['name']);
    }

    // ---------------------------------------------------------------
    // ALL ACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_all_active_returns_only_active_seat_plans(): void
    {
        SeatPlan::factory()->create(['status' => 1]);
        SeatPlan::factory()->create(['status' => 0]);

        $response = $this->getJson('/api/seat-plans/all-active', $this->authHeaders());

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(1, $data[0]['status']);
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_seat_plan(): void
    {
        $response = $this->postJson('/api/seat-plans', $this->validPayload, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201)
            ->assertJsonPath('data.name', 'Test Seat Plan')
            ->assertJsonPath('data.status', 1);

        $this->assertDatabaseHas('seat_plans', ['name' => 'Test Seat Plan', 'floor' => 1, 'status' => 1]);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/seat-plans', [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_invalid_floor_value(): void
    {
        $payload          = $this->validPayload;
        $payload['floor'] = 5;

        $this->postJson('/api/seat-plans', $payload, $this->authHeaders())
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function test_store_fails_when_same_name_and_floor_already_exists(): void
    {
        SeatPlan::factory()->create(['name' => 'Test Seat Plan', 'floor' => 1]);

        $this->postJson('/api/seat-plans', $this->validPayload, $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_store_allows_same_name_with_different_floor(): void
    {
        SeatPlan::factory()->create(['name' => 'Test Seat Plan', 'floor' => 2]);

        $this->postJson('/api/seat-plans', $this->validPayload, $this->authHeaders())
            ->assertStatus(201);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_create_seat_plan(): void
    {
        $this->postJson('/api/seat-plans', $this->validPayload)
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_seat_plan(): void
    {
        $seatPlan = SeatPlan::factory()->create();

        $response = $this->getJson("/api/seat-plans/{$seatPlan->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $seatPlan->id)
            ->assertJsonPath('data.status', $seatPlan->status);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_seat_plan(): void
    {
        $this->getJson('/api/seat-plans/99999', $this->authHeaders())
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_seat_plan(): void
    {
        $seatPlan = SeatPlan::factory()->create();

        $payload = $this->validPayload;
        $payload['name'] = 'Updated Plan Name';

        $response = $this->putJson("/api/seat-plans/{$seatPlan->id}", $payload, $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Updated Plan Name');

        $this->assertDatabaseHas('seat_plans', ['id' => $seatPlan->id, 'name' => 'Updated Plan Name']);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_seat_plan(): void
    {
        $this->putJson('/api/seat-plans/99999', $this->validPayload, $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_update_fails_when_same_name_and_floor_taken_by_another_seat_plan(): void
    {
        SeatPlan::factory()->create(['name' => 'Test Seat Plan', 'floor' => 1]);
        $seatPlan = SeatPlan::factory()->create(['name' => 'Other Plan', 'floor' => 1]);

        $this->putJson("/api/seat-plans/{$seatPlan->id}", $this->validPayload, $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_update_allows_same_name_and_floor_for_same_seat_plan(): void
    {
        $seatPlan = SeatPlan::factory()->create(['name' => 'Test Seat Plan', 'floor' => 1]);

        $this->putJson("/api/seat-plans/{$seatPlan->id}", $this->validPayload, $this->authHeaders())
            ->assertStatus(200);
    }

    // ---------------------------------------------------------------
    // ACTIVE / INACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_activate_seat_plan(): void
    {
        $seatPlan = SeatPlan::factory()->create(['status' => 0]);

        $response = $this->patchJson("/api/seat-plans/{$seatPlan->id}/active", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 1);

        $this->assertDatabaseHas('seat_plans', ['id' => $seatPlan->id, 'status' => 1]);
    }

    /**
     * @test
     */
    public function test_authenticated_user_can_deactivate_seat_plan(): void
    {
        $seatPlan = SeatPlan::factory()->create(['status' => 1]);

        $response = $this->patchJson("/api/seat-plans/{$seatPlan->id}/inactive", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 0);

        $this->assertDatabaseHas('seat_plans', ['id' => $seatPlan->id, 'status' => 0]);
    }

    /**
     * @test
     */
    public function test_active_returns_404_for_nonexistent_seat_plan(): void
    {
        $this->patchJson('/api/seat-plans/99999/active', [], $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_inactive_returns_404_for_nonexistent_seat_plan(): void
    {
        $this->patchJson('/api/seat-plans/99999/inactive', [], $this->authHeaders())
            ->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_delete_seat_plan(): void
    {
        $seatPlan = SeatPlan::factory()->create();

        $response = $this->deleteJson("/api/seat-plans/{$seatPlan->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('seat_plans', ['id' => $seatPlan->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_seat_plan(): void
    {
        $this->deleteJson('/api/seat-plans/99999', [], $this->authHeaders())
            ->assertStatus(404);
    }
}
