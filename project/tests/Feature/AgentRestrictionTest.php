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
        $agent1 = Agent::create(['agent_code' => 'AGT-001', 'name' => 'Agent One', 'commission_rate' => 5.0, 'status' => 'Active']);
        $agent2 = Agent::create(['agent_code' => 'AGT-002', 'name' => 'Agent Two', 'commission_rate' => 5.0, 'status' => 'Active']);

        $agentUser = User::where('email', 'agent@shrishyam.org')->first();
        $agentUser->agent_id = $agent1->id;
        $agentUser->save();

        $scheme = Scheme::create(['code' => 'TEST', 'name' => 'Test Scheme', 'name_hindi' => 'टेस्ट योजना', 'status' => 'Active']);

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
    }
}
