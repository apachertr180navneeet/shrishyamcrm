<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Agent;
use App\Models\Member;

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
        $agentUser = User::where('email', 'agent1@shrishyam.org')->first();
        $this->assertNotNull($agentUser);
        $this->assertNotNull($agentUser->agent_id);

        $response = $this->actingAs($agentUser)->get(route('admin.members.index'));
        $response->assertStatus(200);

        // Verify that all members passed to the view belong only to this agent
        $viewMembers = $response->viewData('members');
        foreach ($viewMembers as $member) {
            $this->assertEquals($agentUser->agent_id, $member->agent_id);
        }
    }
}
