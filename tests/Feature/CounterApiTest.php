<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CounterApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_name' => 'testuser',
            'email'     => 'test@example.com',
            'password'  => bcrypt('password'),
            'status'    => 1,
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
    public function test_authenticated_user_can_list_counters(): void
    {
        Counter::factory()->count(3)->create();

        $response = $this->getJson('/api/counters', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [[
                    'id', 'type', 'address', 'land_mark', 'location_url',
                    'phone', 'mobile', 'email', 'primary_contact_no', 'country',
                    'district_id', 'booking_allowed_status', 'booking_allowed_class',
                    'no_of_boarding_allowed', 'sms_status', 'status',
                    'created_by', 'updated_by', 'created_at', 'updated_at'
                ]],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_counters(): void
    {
        $this->getJson('/api/counters')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_index_supports_search(): void
    {
        Counter::factory()->create(['address' => 'Dhaka Airport', 'land_mark' => 'Terminal 3']);
        Counter::factory()->create(['address' => 'Chittagong Port', 'land_mark' => 'Gate 1']);

        $response = $this->getJson('/api/counters?search=Airport', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ---------------------------------------------------------------
    // ALL ACTIVE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_list_all_active_counters(): void
    {
        Counter::factory()->create(['status' => '1']);
        Counter::factory()->create(['status' => '0']);

        $response = $this->getJson('/api/counters/all-active', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [[
                    'id', 'type', 'address', 'land_mark', 'status'
                ]],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_counter(): void
    {
        $district = District::factory()->create();

        $payload = [
            'type'                   => 1,
            'address'                => 'Main Terminal',
            'land_mark'              => 'Next to food court',
            'location_url'           => 'http://maps.google.com/test',
            'phone'                  => '1234567890',
            'mobile'                 => '0987654321',
            'email'                  => 'counter@example.com',
            'primary_contact_no'     => '1122334455',
            'country'                => 'Bangladesh',
            'district_id'            => $district->id,
            'booking_allowed_status' => 2,
            'booking_allowed_class'  => 3,
            'no_of_boarding_allowed' => 5,
            'sms_status'             => 1,
            'status'                 => '1',
        ];

        $response = $this->postJson('/api/counters', $payload, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201)
            ->assertJsonPath('data.address', 'Main Terminal');

        $this->assertDatabaseHas('counters', [
            'address' => 'Main Terminal',
            'email'   => 'counter@example.com',
        ]);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/counters', [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_create_counter(): void
    {
        $this->postJson('/api/counters', ['address' => 'Some address'])
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_counter(): void
    {
        $counter = Counter::factory()->create();

        $response = $this->getJson("/api/counters/{$counter->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $counter->id)
            ->assertJsonPath('data.address', $counter->address);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_counter(): void
    {
        $this->getJson('/api/counters/99999', $this->authHeaders())
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_counter(): void
    {
        $counter  = Counter::factory()->create(['address' => 'Old Address']);
        $district = District::factory()->create();

        $payload = [
            'type'                   => 2,
            'address'                => 'New Address',
            'land_mark'              => 'Near Bus Stand',
            'location_url'           => 'http://maps.google.com/test2',
            'phone'                  => '999999',
            'mobile'                 => '888888',
            'email'                  => 'newcounter@example.com',
            'primary_contact_no'     => '777777',
            'country'                => 'Bangladesh',
            'district_id'            => $district->id,
            'booking_allowed_status' => 1,
            'booking_allowed_class'  => 2,
            'no_of_boarding_allowed' => 10,
            'sms_status'             => 2,
            'status'                 => '1',
        ];

        $response = $this->putJson("/api/counters/{$counter->id}", $payload, $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.address', 'New Address');

        $this->assertDatabaseHas('counters', [
            'id'      => $counter->id,
            'address' => 'New Address',
        ]);
    }

    /**
     * @test
     */
    public function test_update_fails_with_missing_required_fields(): void
    {
        $counter = Counter::factory()->create();

        $this->putJson("/api/counters/{$counter->id}", [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_counter(): void
    {
        $this->putJson('/api/counters/99999', ['address' => 'Anywhere'], $this->authHeaders())
            ->assertStatus(422);
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_delete_counter(): void
    {
        $counter = Counter::factory()->create();

        $response = $this->deleteJson("/api/counters/{$counter->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertSoftDeleted('counters', ['id' => $counter->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_counter(): void
    {
        $this->deleteJson('/api/counters/99999', [], $this->authHeaders())
            ->assertStatus(404);
    }
}
