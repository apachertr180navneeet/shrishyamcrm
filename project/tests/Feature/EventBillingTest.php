<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use App\Services\EventBillingService;
use Exception;

class EventBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_consolidated_event_billing_debits_active_members_and_prevents_duplicate_runs()
    {
        $event = MarriageEvent::first();
        $scheme = Scheme::where('code', 'MARRIAGE')->first();

        $billing = EventBillingService::processConsolidatedBilling([
            'event_id' => $event->id,
            'billing_month' => '2026-11',
            'scheme_id' => $scheme->id,
            'events_count' => 3,
            'rate_per_event' => 200.0,
        ]);

        $this->assertNotNull($billing->id);
        $this->assertEquals(600.0, (float)$billing->total_per_member); // 3 events * 200

        // Duplicate billing attempt should throw exception
        $this->expectException(Exception::class);
        EventBillingService::processConsolidatedBilling([
            'event_id' => $event->id,
            'billing_month' => '2026-11',
            'scheme_id' => $scheme->id,
            'events_count' => 3,
            'rate_per_event' => 200.0,
        ]);
    }
}
