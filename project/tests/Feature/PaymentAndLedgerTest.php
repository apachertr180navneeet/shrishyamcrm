<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Member;
use App\Models\Agent;
use App\Models\Scheme;
use App\Services\PaymentService;
use App\Services\LedgerService;

class PaymentAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_partial_payment_updates_ledger_and_carries_forward_outstanding_balance()
    {
        $agent = Agent::first();
        $scheme = Scheme::first();

        // Create a new member with 0 initial pending balance
        $member = Member::create([
            'membership_no' => 'MEM-TEST-9999',
            'full_name' => 'Partial Payment Tester',
            'mobile' => '9800000000',
            'district' => 'Mahendragarh',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'status' => 'Active',
            'pending_amount' => 0,
            'total_paid' => 0,
        ]);

        // 1. Post a monthly charge of 1000
        LedgerService::postEntry(
            memberId: $member->id,
            entryType: 'Monthly Due',
            description: 'August 2026 Monthly Due',
            debit: 1000.0,
            credit: 0.0
        );

        $member->refresh();
        $this->assertEquals(1000.0, (float)$member->pending_amount);

        // 2. Make partial payment of 400
        $payment1 = PaymentService::processPayment([
            'member_id' => $member->id,
            'amount' => 400.0,
            'payment_type' => 'Monthly Support',
            'payment_mode' => 'Cash',
        ]);

        $member->refresh();
        // Remaining outstanding should be 600
        $this->assertEquals(600.0, (float)$member->pending_amount);

        // Verify commission recorded on payment
        $this->assertDatabaseHas('agent_commissions', [
            'payment_id' => $payment1->id,
            'collection_amount' => 400.0,
            'commission_amount' => 20.0, // 5% of 400
        ]);

        // 3. Complete remaining payment of 600
        $payment2 = PaymentService::processPayment([
            'member_id' => $member->id,
            'amount' => 600.0,
            'payment_type' => 'Monthly Support',
            'payment_mode' => 'UPI',
        ]);

        $member->refresh();
        $this->assertEquals(0.0, (float)$member->pending_amount);
    }
}
