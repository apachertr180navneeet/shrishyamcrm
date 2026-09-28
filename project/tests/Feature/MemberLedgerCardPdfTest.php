<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\Agent;
use App\Models\User;
use App\Models\MarriageEvent;
use App\Models\EventContribution;
use App\Models\Payment;

class MemberLedgerCardPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_download_member_ledger_card_pdf_with_paid_and_pending_events()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_test_ledger@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'BUZURG',
            'name' => 'Buzurg Samman Yojana',
            'name_hindi' => 'बुजुर्ग सम्मान योजना',
            'status' => 'Active'
        ]);

        $agent = Agent::create([
            'agent_code' => 'AGT-001',
            'name' => 'बुधराम',
            'mobile' => '9783049650',
            'status' => 'Active',
            'district' => 'बालोतरा',
        ]);

        $member = Member::create([
            'membership_no' => 'MEM-2026-1008',
            'full_name' => 'चौथी देवी',
            'father_spouse_name' => 'संतराम',
            'gender' => 'Female',
            'dob' => '1960-05-15',
            'age' => 66,
            'mobile' => '6375635153',
            'address' => 'चाडो की ढाणी',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'monthly_support_amount' => 400.00,
            'status' => 'Active',
            'joining_date' => now()->subMonths(6),
        ]);

        // Create Nominee
        \App\Models\Nominee::create([
            'member_id' => $member->id,
            'name' => 'मदनलाल',
            'relation' => 'वारिसदार',
            'mobile' => '9876543210',
            'priority' => 1,
        ]);

        // Event 1 (Paid)
        $event1 = MarriageEvent::create([
            'event_code' => 'EVT-01',
            'title' => 'कल्याण सहायता कार्यक्रम',
            'girl_name' => 'अगरोजी / हरताजी भाटी चाडों की ढाणी',
            'event_date' => '2026-01-28',
            'target_amount' => 10000,
            'status' => 'Completed',
        ]);

        $payment1 = Payment::create([
            'receipt_no' => 'RCP-001',
            'member_id' => $member->id,
            'agent_id' => $agent->id,
            'amount' => 400.00,
            'payment_type' => 'Event Contribution',
            'payment_mode' => 'Cash',
            'payment_date' => '2026-01-28',
            'status' => 'Verified',
        ]);

        EventContribution::create([
            'event_id' => $event1->id,
            'member_id' => $member->id,
            'event_name' => $event1->title,
            'event_date' => '2026-01-28',
            'member_name' => $member->full_name,
            'contribution_amount' => 400.00,
            'payment_status' => 'Paid',
            'payment_date' => '2026-01-28',
            'payment_id' => $payment1->id,
            'agent_id' => $agent->id,
        ]);

        // Event 2 (Pending)
        $event2 = MarriageEvent::create([
            'event_code' => 'EVT-02',
            'title' => 'कल्याण सहायता कार्यक्रम',
            'girl_name' => 'चौथाजी / हेमाजी हो की ढाणी',
            'event_date' => '2026-02-15',
            'target_amount' => 10000,
            'status' => 'Active',
        ]);

        EventContribution::create([
            'event_id' => $event2->id,
            'member_id' => $member->id,
            'event_name' => $event2->title,
            'event_date' => '2026-02-15',
            'member_name' => $member->full_name,
            'contribution_amount' => 400.00,
            'payment_status' => 'Pending',
            'agent_id' => $agent->id,
        ]);

        // Test download via member ledger route
        $response = $this->actingAs($admin)->get(route('admin.members.ledger.pdf', $member->id));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        // Test download via ledger page route
        $response2 = $this->actingAs($admin)->get(route('admin.ledger.pdf', $member->id));
        $response2->assertOk();
        $response2->assertHeader('content-type', 'application/pdf');
    }
}
