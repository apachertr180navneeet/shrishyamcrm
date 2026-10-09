<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Agent;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AuthRoleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_login_with_email_successfully()
    {
        // Admin seeded user
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->post(route('admin.login.post'), [
            'login_type' => 'admin',
            'email' => $admin->email,
            'password' => 'ShriShyam@123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_tab_rejects_non_email_input()
    {
        $response = $this->post(route('admin.login.post'), [
            'login_type' => 'admin',
            'email' => '8000000000',
            'password' => 'ShriShyam@123',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('ईमेल पता', session('error'));
        $this->assertGuest();
    }

    public function test_agent_can_login_with_mobile_successfully()
    {
        // Agent seeded user
        $agentUser = User::where('role', 'agent')->first();
        $this->assertNotNull($agentUser);
        $this->assertNotEmpty($agentUser->phone);

        $response = $this->post(route('admin.login.post'), [
            'login_type' => 'agent',
            'email' => $agentUser->phone,
            'password' => 'ShriShyam@123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($agentUser);
    }

    public function test_agent_tab_rejects_email_input()
    {
        $agentUser = User::where('role', 'agent')->first();
        $this->assertNotNull($agentUser);

        $response = $this->post(route('admin.login.post'), [
            'login_type' => 'agent',
            'email' => $agentUser->email,
            'password' => 'ShriShyam@123',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('मोबाइल नंबर', session('error'));
        $this->assertGuest();
    }

    public function test_agent_email_on_admin_tab_is_blocked_with_guidance()
    {
        $agentUser = User::where('role', 'agent')->first();
        $this->assertNotNull($agentUser);

        $response = $this->post(route('admin.login.post'), [
            'login_type' => 'admin',
            'email' => $agentUser->email,
            'password' => 'ShriShyam@123',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('कार्यकर्ता कृपया कार्यकर्ता (Agent) टैब', session('error'));
        $this->assertGuest();
    }

    public function test_admin_mobile_on_agent_tab_is_blocked_with_guidance()
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->post(route('admin.login.post'), [
            'login_type' => 'agent',
            'email' => $admin->phone,
            'password' => 'ShriShyam@123',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('एडमिन कृपया एडमिन (Admin) टैब', session('error'));
        $this->assertGuest();
    }
}
