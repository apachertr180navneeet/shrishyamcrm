<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Agent;
use App\Models\Member;
use App\Models\Scheme;

class AgentRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_agent_user_can_only_access_their_assigned_members()
    {
        $agent1 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-1'],
            ['name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9800000010']
        );
        $agent2 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-2'],
            ['name' => 'Agent Two', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Sikar', 'mobile' => '9800000020']
        );

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $scheme = Scheme::firstOrCreate(
            ['code' => 'TEST_SCHEME'],
            ['name' => 'Test Scheme', 'name_hindi' => 'टेस्ट योजना', 'status' => 'Active']
        );

        // Member assigned to agent 1
        $member1 = Member::create([
            'membership_no' => 'MEM-A1',
            'full_name' => 'Member Agent 1',
            'mobile' => '9800000001',
            'agent_id' => $agent1->id,
            'scheme_id' => $scheme->id,
            'status' => 'Active',
        ]);

        // Member assigned to agent 2
        $member2 = Member::create([
            'membership_no' => 'MEM-A2',
            'full_name' => 'Member Agent 2',
            'mobile' => '9800000002',
            'agent_id' => $agent2->id,
            'scheme_id' => $scheme->id,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($agentUser)->get(route('admin.members.index'));
        $response->assertStatus(200);

        // Verify that only agent1's members are returned
        $viewMembers = $response->viewData('members');
        foreach ($viewMembers as $member) {
            $this->assertEquals($agent1->id, $member->agent_id);
        }

        // Verify agents list in view only contains agent1
        $viewAgents = $response->viewData('agents');
        $this->assertCount(1, $viewAgents);
        $this->assertEquals($agent1->id, $viewAgents->first()->id);
    }

    public function test_agent_user_only_sees_own_agent_in_member_create_form()
    {
        $agent1 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-1'],
            ['name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9800000010']
        );

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $response = $this->actingAs($agentUser)->get(route('admin.members.create'));
        $response->assertStatus(200);

        $viewAgents = $response->viewData('agents');
        $this->assertCount(1, $viewAgents);
        $this->assertEquals($agent1->id, $viewAgents->first()->id);
    }

    public function test_agent_user_only_sees_own_agent_in_payment_create_form()
    {
        $agent1 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-1'],
            ['name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9800000010']
        );

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $response = $this->actingAs($agentUser)->get(route('admin.payments.create'));
        $response->assertStatus(200);

        $viewAgents = $response->viewData('agents');
        $this->assertCount(1, $viewAgents);
        $this->assertEquals($agent1->id, $viewAgents->first()->id);
    }

    public function test_agent_user_cannot_access_agents_index()
    {
        $agent1 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-1'],
            ['name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9800000010']
        );

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $response = $this->actingAs($agentUser)->get(route('admin.agents.index'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_agent_user_cannot_access_agents_show()
    {
        $agent1 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-1'],
            ['name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9800000010']
        );
        $agent2 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-2'],
            ['name' => 'Agent Two', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Sikar', 'mobile' => '9800000020']
        );

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $response = $this->actingAs($agentUser)->get(route('admin.agents.show', $agent2->id));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_agent_user_cannot_create_new_agent()
    {
        $agent1 = Agent::firstOrCreate(
            ['agent_code' => 'AGT-TEST-1'],
            ['name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9800000010']
        );

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $response = $this->actingAs($agentUser)->post(route('admin.agents.store'), [
            'name' => 'Unauthorized Agent',
            'mobile' => '9999999999',
            'district' => 'Delhi',
            'commission_rate' => 5,
        ]);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_view_and_update_agent_login_credentials()
    {
        $admin = User::where('email', 'admin@shrishyam.org')->first();
        $agent = Agent::firstOrCreate(
            ['agent_code' => 'AGT-CRED-1'],
            ['name' => 'Credential Agent', 'commission_rate' => 5.0, 'status' => 'Active', 'district' => 'Jaipur', 'mobile' => '9812345678']
        );

        // 1. Get credentials API
        $response = $this->actingAs($admin)->get(route('admin.agents.credentials', $agent->id));
        $response->assertOk();
        $response->assertJsonStructure(['status', 'login_url', 'username', 'whatsapp_url', 'message']);
        $this->assertStringContainsString('9812345678', $response->json('message'));

        // 2. Update credentials API
        $updateResp = $this->actingAs($admin)->postJson(route('admin.agents.credentials.update', $agent->id), [
            'password' => 'Shyam@9876',
            'mobile' => '9812345678',
        ]);
        $updateResp->assertOk();
        $updateResp->assertJson(['status' => 'success']);
        $this->assertStringContainsString('Shyam@9876', $updateResp->json('credentials_message'));

        // 3. Test logging in with 10-digit Mobile number
        $loginResp = $this->post(route('admin.login.post'), [
            'email' => '9812345678',
            'password' => 'Shyam@9876',
        ]);
        $loginResp->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        // Logout
        $this->post(route('admin.logout'));
        $this->assertGuest();

        // 4. Test logging in with country code prefix (+91 9812345678)
        $loginWithCountryCode = $this->post(route('admin.login.post'), [
            'email' => '+91 9812345678',
            'password' => 'Shyam@9876',
        ]);
        $loginWithCountryCode->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }
}
