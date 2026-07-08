<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SystemSettingApiTest extends TestCase
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

    /**
     * @test
     */
    public function test_public_user_can_access_website_settings_info(): void
    {
        SystemSetting::updateOrCreate(['key' => 'website_name'], ['value' => 'Imperial Public Bus']);
        SystemSetting::updateOrCreate(['key' => 'website_email'], ['value' => 'public@imperial.com']);

        $response = $this->getJson('/api/info');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Imperial Public Bus')
            ->assertJsonPath('data.email', 'public@imperial.com');
    }

    /**
     * @test
     */
    public function test_authenticated_user_can_list_system_settings(): void
    {
        SystemSetting::updateOrCreate(['key' => 'name'], ['value' => 'Imperial Admin Bus']);

        $response = $this->getJson('/api/system-settings', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Imperial Admin Bus');
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_system_settings(): void
    {
        $this->getJson('/api/system-settings')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_authenticated_user_can_update_system_settings(): void
    {
        $payload = [
            'name' => 'Imperial System Bus Updated',
            'email' => 'info@imperial.com',
            'phone' => '+1234567890',
            'address' => 'Updated Address',
            'data_per_page' => 15,
            'currency_symbol' => '$',
            'currency_name' => 'USD',
            'currency_position' => 'before',
            'currency_decimal_point' => 2,
            'print_footer_message' => 'Thank you for choosing Imperial Bus',
            'date_format' => 'd-m-Y',
            'time_format' => 'h:i A',
        ];

        $response = $this->patchJson('/api/system-settings', $payload, $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'System settings updated successfully.');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'name',
            'value' => 'Imperial System Bus Updated',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'data_per_page',
            'value' => '15',
        ]);
    }

    /**
     * @test
     */
    public function test_authenticated_user_can_list_website_settings(): void
    {
        SystemSetting::updateOrCreate(['key' => 'website_name'], ['value' => 'Imperial Web Portal']);

        $response = $this->getJson('/api/website-settings', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Imperial Web Portal');
    }

    /**
     * @test
     */
    public function test_unauthenticated_user_cannot_list_website_settings(): void
    {
        $this->getJson('/api/website-settings')
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_authenticated_user_can_update_website_settings(): void
    {
        $payload = [
            'name' => 'Imperial Web Updated',
            'email' => 'web@imperial.com',
            'phone' => '+0987654321',
            'address' => 'Website Address',
            'opening_time' => '08:00 AM',
            'closing_time' => '10:00 PM',
            'google_map' => '<iframe></iframe>',
            'copyright' => 'Copyright 2026 Imperial',
            'footer_text' => 'Footer Info',
            'currency_symbol' => '$',
            'currency_name' => 'USD',
            'currency_position' => 'before',
            'currency_decimal_point' => 2,
        ];

        $response = $this->patchJson('/api/website-settings', $payload, $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Website settings updated successfully.');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'website_name',
            'value' => 'Imperial Web Updated',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'website_email',
            'value' => 'web@imperial.com',
        ]);
    }
}
