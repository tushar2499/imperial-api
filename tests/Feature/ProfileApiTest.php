<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_name' => 'testuser',
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 1,
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ---------------------------------------------------------------
    // GET /api/user
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_get_their_own_data(): void
    {
        $this->getJson('/api/user', $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $this->user->id)
            ->assertJsonPath('data.user_name', 'testuser')
            ->assertJsonStructure([
                'data' => ['id', 'user_name', 'first_name', 'email', 'photo', 'status'],
            ]);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_get_user_data(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // GET /api/profile
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_view_profile(): void
    {
        $this->getJson('/api/profile', $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $this->user->id)
            ->assertJsonPath('data.user_name', 'testuser');
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $this->getJson('/api/profile')->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_profile_photo_is_returned_as_asset_url(): void
    {
        $this->user->update(['photo' => 'uploads/users/test.jpg']);

        $response = $this->getJson('/api/profile', $this->authHeaders());

        $response->assertStatus(200);
        $this->assertStringContainsString('uploads/users/test.jpg', $response->json('data.photo'));
    }

    /**
     * @test
     */
    public function test_profile_photo_is_null_when_not_set(): void
    {
        $this->user->update(['photo' => null]);

        $this->getJson('/api/profile', $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonPath('data.photo', null);
    }

    // ---------------------------------------------------------------
    // PUT /api/profile
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_profile(): void
    {
        $response = $this->putJson('/api/profile', [
            'user_name' => 'updateduser',
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user_name', 'updateduser')
            ->assertJsonPath('data.first_name', 'Updated');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'user_name' => 'updateduser',
        ]);
    }

    /**
     * @test
     */
    public function test_update_profile_fails_with_missing_required_fields(): void
    {
        $this->putJson('/api/profile', [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_update_profile_allows_same_username_for_same_user(): void
    {
        $this->putJson('/api/profile', [
            'user_name' => 'testuser',
            'first_name' => 'Test',
        ], $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    /**
     * @test
     */
    public function test_update_profile_fails_when_username_taken_by_another_user(): void
    {
        User::factory()->create(['user_name' => 'otherusername']);

        $this->putJson('/api/profile', [
            'user_name' => 'otherusername',
            'first_name' => 'Test',
        ], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $this->putJson('/api/profile', [
            'user_name' => 'hacker',
            'first_name' => 'Hacker',
        ])->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // POST /api/profile/photo
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_update_profile_photo(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->postJson('/api/profile/photo', [
            'photo' => $file,
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['photo']]);

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
        $this->assertNotNull($response->json('data.photo'));
    }

    /**
     * @test
     */
    public function test_update_photo_fails_without_a_file(): void
    {
        $this->postJson('/api/profile/photo', [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_update_photo_fails_with_non_image_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->postJson('/api/profile/photo', [
            'photo' => $file,
        ], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_update_photo(): void
    {
        $this->postJson('/api/profile/photo', [
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ])->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // POST /api/profile/password
    // ---------------------------------------------------------------

    /**
     * @test
     */
    public function test_authenticated_user_can_change_password(): void
    {
        $response = $this->postJson('/api/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    /**
     * @test
     */
    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $this->postJson('/api/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_change_password_fails_with_mismatched_confirmation(): void
    {
        $this->postJson('/api/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /**
     * @test
     */
    public function test_change_password_fails_with_missing_fields(): void
    {
        $this->postJson('/api/profile/password', [], $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $this->postJson('/api/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(401);
    }
}
