<?php

namespace Tests\Feature;

use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmployeeApiTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Doe',
            'contact_no' => '01700000001',
            'date_of_birth' => '1990-05-15',
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_list_employees(): void
    {
        Employee::factory()->count(3)->create();

        $response = $this->getJson('/api/employees', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [['id', 'name', 'contact_no']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_employees(): void
    {
        $this->getJson('/api/employees')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_index_supports_search_by_name(): void
    {
        Employee::factory()->create(['name' => 'Alice Smith']);
        Employee::factory()->create(['name' => 'Bob Jones']);

        $response = $this->getJson('/api/employees?search=Alice', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    /**
     * @test
     */
    public function test_index_supports_pagination(): void
    {
        Employee::factory()->count(5)->create();

        $response = $this->getJson('/api/employees?per_page=2&page=1', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5);
    }

    // ---------------------------------------------------------------
    // STORE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_create_employee(): void
    {
        $response = $this->postJson('/api/employees', $this->validPayload(), $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 201)
            ->assertJsonPath('data.name', 'John Doe');

        $this->assertDatabaseHas('employees', ['name' => 'John Doe', 'contact_no' => '01700000001']);
    }

    /**
     * @test
     */
    public function test_store_creates_employee_with_academics_and_experiences(): void
    {
        $payload = $this->validPayload([
            'academics' => [
                ['degree' => 'BSc', 'institute' => 'BUET', 'field_of_study' => 'CSE'],
            ],
            'experiences' => [
                ['organization' => 'Acme Corp', 'position' => 'Engineer'],
            ],
        ]);

        $response = $this->postJson('/api/employees', $payload, $this->authHeaders());

        $response->assertStatus(201);

        $employee = Employee::where('name', 'John Doe')->first();
        $this->assertNotNull($employee);
        $this->assertDatabaseHas('employee_academics', ['employee_id' => $employee->id, 'degree' => 'BSc']);
        $this->assertDatabaseHas('employee_experiences', ['employee_id' => $employee->id, 'organization' => 'Acme Corp']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/employees', [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_store_fails_with_invalid_date_format(): void
    {
        $response = $this->postJson('/api/employees', $this->validPayload([
            'date_of_birth' => '15-05-1990',
        ]), $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_store_fails_with_nonexistent_district(): void
    {
        $response = $this->postJson('/api/employees', $this->validPayload([
            'district_id' => 99999,
        ]), $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_store_fails_when_academics_missing_required_degree(): void
    {
        $payload = $this->validPayload([
            'academics' => [
                ['institute' => 'BUET'],
            ],
        ]);

        $response = $this->postJson('/api/employees', $payload, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_create_employee(): void
    {
        $this->postJson('/api/employees', $this->validPayload())
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // SHOW
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->getJson("/api/employees/{$employee->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $employee->id)
            ->assertJsonPath('data.name', $employee->name)
            ->assertJsonStructure(['data' => ['academics', 'experiences']]);
    }

    /**
     * @test
     */
    public function test_show_returns_404_for_nonexistent_employee(): void
    {
        $this->getJson('/api/employees/99999', $this->authHeaders())
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_view_employee(): void
    {
        $employee = Employee::factory()->create();

        $this->getJson("/api/employees/{$employee->id}")
            ->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_employee(): void
    {
        $employee = Employee::factory()->create(['name' => 'OldName']);

        $response = $this->putJson("/api/employees/{$employee->id}", $this->validPayload([
            'name' => 'NewName',
        ]), $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'NewName');

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => 'NewName']);
    }

    /**
     * @test
     */
    public function test_update_replaces_academics_and_experiences(): void
    {
        $employee = Employee::factory()->create();
        $employee->academics()->create(['degree' => 'BSc', 'institute' => 'BUET']);
        $employee->experiences()->create(['organization' => 'OldCorp', 'position' => 'Dev']);

        $payload = $this->validPayload([
            'academics' => [['degree' => 'MSc', 'institute' => 'DU']],
            'experiences' => [['organization' => 'NewCorp', 'position' => 'Lead']],
        ]);

        $this->putJson("/api/employees/{$employee->id}", $payload, $this->authHeaders())
            ->assertStatus(200);

        $this->assertDatabaseHas('employee_academics', ['employee_id' => $employee->id, 'degree' => 'MSc']);
        $this->assertDatabaseMissing('employee_academics', ['employee_id' => $employee->id, 'degree' => 'BSc']);
        $this->assertDatabaseHas('employee_experiences', ['employee_id' => $employee->id, 'organization' => 'NewCorp']);
        $this->assertDatabaseMissing('employee_experiences', ['employee_id' => $employee->id, 'organization' => 'OldCorp']);
    }

    /**
     * @test
     */
    public function test_update_fails_with_missing_required_fields(): void
    {
        $employee = Employee::factory()->create();

        $this->putJson("/api/employees/{$employee->id}", [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_update_returns_404_for_nonexistent_employee(): void
    {
        $this->putJson('/api/employees/99999', $this->validPayload(), $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_update_with_valid_designation(): void
    {
        $designation = Designation::factory()->create();
        $employee = Employee::factory()->create();

        $response = $this->putJson("/api/employees/{$employee->id}", $this->validPayload([
            'designation_id' => $designation->id,
        ]), $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.designation_id', $designation->id);
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_delete_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->deleteJson("/api/employees/{$employee->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }

    /**
     * @test
     */
    public function test_destroy_also_removes_academics_and_experiences(): void
    {
        $employee = Employee::factory()->create();
        $employee->academics()->create(['degree' => 'BSc', 'institute' => 'BUET']);
        $employee->experiences()->create(['organization' => 'Acme', 'position' => 'Dev']);

        $this->deleteJson("/api/employees/{$employee->id}", [], $this->authHeaders())
            ->assertStatus(200);

        $this->assertDatabaseMissing('employee_academics', ['employee_id' => $employee->id]);
        $this->assertDatabaseMissing('employee_experiences', ['employee_id' => $employee->id]);
    }

    /**
     * @test
     */
    public function test_destroy_returns_404_for_nonexistent_employee(): void
    {
        $this->deleteJson('/api/employees/99999', [], $this->authHeaders())
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_delete_employee(): void
    {
        $employee = Employee::factory()->create();

        $this->deleteJson("/api/employees/{$employee->id}")
            ->assertStatus(401);
    }
}
