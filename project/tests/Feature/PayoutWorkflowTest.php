<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MarriageEvent;
use App\Models\Payout;
use App\Services\PayoutService;

class PayoutWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_payout_workflow_from_creation_to_approval_and_disbursement()
    {
        $event = MarriageEvent::first();

        // 1. Create Payout
        $payout = PayoutService::createPayout([
            'event_id' => $event->id,
            'beneficiary_name' => 'Kailash Devi (Mother)',
            'relation' => 'Mother',
            'payout_type' => 'Marriage Assistance',
            'amount' => 51000.0,
            'payment_mode' => 'Bank Transfer',
            'status' => 'Pending Approval',
        ]);

        $this->assertNotNull($payout->id);
        $this->assertStringStartsWith('PAY-', $payout->payout_no);
        $this->assertEquals('Pending Approval', $payout->status);

        // 2. Approve Payout
        $approvedPayout = PayoutService::updateStatus($payout->id, 'Approved', 'Approved by Managing Committee');
        $this->assertEquals('Approved', $approvedPayout->status);

        // 3. Disburse Payout
        $disbursedPayout = PayoutService::updateStatus($payout->id, 'Disbursed', 'Amount transferred via RTGS');
        $this->assertEquals('Disbursed', $disbursedPayout->status);
    }
}
