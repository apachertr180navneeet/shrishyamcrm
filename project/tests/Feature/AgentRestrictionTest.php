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
}
