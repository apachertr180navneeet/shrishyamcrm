<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\User;
use App\Models\Payment;
use App\Models\EventContribution;
use App\Services\WhatsAppService;
use App\Services\ContributionCalculationService;

class InactiveMemberSafeguardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_marriage_event_creation_closes_beneficiary_membership_and_excludes_them_from_contributions()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_inactive_test@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'KANYA-SAFE',
            'name' => 'Kanya Vivah Scheme Safe',
            'name_hindi' => 'कन्या विवाह',
            'status' => 'Active'
        ]);

        // Create 3 active members
        $beneficiary = Member::create([
            'membership_no' => 'SHYAM-BEN-001',
            'full_name' => 'सुनीता कुमारी',
            'father_spouse_name' => 'राम पाल',
            'gender' => 'Female',
            'dob' => '2000-01-01',
            'age' => 26,
            'mobile' => '9876500010',
            'scheme_id' => $scheme->id,
            'status' => 'Active',
            'joining_date' => now()->subMonths(12),
        ]);

        $activeMember1 = Member::create([
            'membership_no' => 'SHYAM-ACT-001',
            'full_name' => 'विकास कुमार',
            'father_spouse_name' => 'हरीश कुमार',
            'gender' => 'Male',
            'dob' => '1995-01-01',
            'age' => 31,
            'mobile' => '9876500011',
            'scheme_id' => $scheme->id,
            'status' => 'Active',
            'joining_date' => now()->subMonths(12),
        ]);

        $alreadyInactiveMember = Member::create([
            'membership_no' => 'SHYAM-INACT-001',
            'full_name' => 'पुरानी सदस्या',
            'father_spouse_name' => 'ओम प्रकाश',
            'gender' => 'Female',
            'dob' => '1992-01-01',
            'age' => 34,
            'mobile' => '9876500012',
            'scheme_id' => $scheme->id,
            'status' => 'Inactive',
            'joining_date' => now()->subMonths(24),
        ]);

        // Create Marriage Event for $beneficiary
        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'सुनीता विवाह कार्यक्रम',
            'event_type' => 'विवाह',
            'girl_name' => 'सुनीता कुमारी',
            'beneficiary_name' => 'सुनीता कुमारी',
            'father_name' => 'राम पाल',
            'member_id' => $beneficiary->id,
            'scheme_id' => $scheme->id,
            'event_date' => '2026-10-15',
            'venue' => 'श्री श्याम धर्मशाला, लोहीकी',
            'target_amount' => 51000,
            'rate_per_event' => 200,
        ]);

        $response->assertSessionHasNoErrors();

        // 1. Beneficiary member must now be Inactive
        $beneficiary->refresh();
        $this->assertEquals('Inactive', $beneficiary->status);

        // 2. Beneficiary member must NOT have an EventContribution generated for their own event
        $event = MarriageEvent::where('girl_name', 'सुनीता कुमारी')->first();
        $this->assertNotNull($event);

        $beneficiaryContribution = EventContribution::where('event_id', $event->id)
            ->where('member_id', $beneficiary->id)
            ->first();
        $this->assertNull($beneficiaryContribution, 'Beneficiary should not be billed for their own marriage event');

        // 3. Pre-existing Inactive member must NOT have an EventContribution
        $inactiveContribution = EventContribution::where('event_id', $event->id)
            ->where('member_id', $alreadyInactiveMember->id)
            ->first();
        $this->assertNull($inactiveContribution, 'Pre-existing inactive member should not receive event contributions');

        // 4. Active member MUST have an EventContribution
        $activeContribution = EventContribution::where('event_id', $event->id)
            ->where('member_id', $activeMember1->id)
            ->first();
        $this->assertNotNull($activeContribution, 'Active member should receive event contribution debit');

        // 5. Create a SECOND event and verify Sunita (now inactive) does NOT get billed for it
        $event2 = MarriageEvent::create([
            'event_code' => 'EVT-TEST-02',
            'title' => 'अन्य विवाह कार्यक्रम',
            'event_type' => 'विवाह',
            'girl_name' => 'रेखा कुमारी',
            'father_name' => 'महेश चंद',
            'scheme_id' => $scheme->id,
            'event_date' => '2026-10-25',
            'venue' => 'श्री श्याम धर्मशाला',
            'target_amount' => 51000,
            'status' => 'Active',
        ]);

        ContributionCalculationService::generateEventContributions($event2);

        $sunitaContribution2 = EventContribution::where('event_id', $event2->id)
            ->where('member_id', $beneficiary->id)
            ->first();
        $this->assertNull($sunitaContribution2, 'Inactive member Sunita should not receive contributions for future events');
    }

    public function test_whatsapp_service_and_controller_block_inactive_members()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_wa_test@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'KANYA-WA',
            'name' => 'Kanya Scheme',
            'name_hindi' => 'कन्या विवाह',
            'status' => 'Active'
        ]);

        $inactiveMember = Member::create([
            'membership_no' => 'SHYAM-INACT-WA',
            'full_name' => 'सीमा रानी',
            'father_spouse_name' => 'राजेश',
            'gender' => 'Female',
            'dob' => '1995-01-01',
            'age' => 31,
            'mobile' => '9876543210',
            'scheme_id' => $scheme->id,
            'status' => 'Inactive',
            'joining_date' => now()->subMonths(10),
            'pending_amount' => 400,
        ]);

        $payment = Payment::create([
            'receipt_no' => 'REC-TEST-WA-01',
            'member_id' => $inactiveMember->id,
            'amount' => 500,
            'payment_type' => 'Monthly Support',
            'payment_mode' => 'Cash',
            'payment_date' => now(),
            'status' => 'Verified',
        ]);

        // WhatsAppService::getReceiptMessage should return disabled
        $receiptMsg = WhatsAppService::getReceiptMessage($payment);
        $this->assertTrue($receiptMsg['disabled']);
        $this->assertEquals('#', $receiptMsg['url']);

        // WhatsAppService::getDueReminderMessage should return disabled
        $dueMsg = WhatsAppService::getDueReminderMessage($inactiveMember);
        $this->assertTrue($dueMsg['disabled']);
        $this->assertEquals('#', $dueMsg['url']);

        // WhatsAppController::send should reject message to inactive member
        $response = $this->actingAs($admin)->post(route('admin.whatsapp.send'), [
            'member_id' => $inactiveMember->id,
            'recipient_name' => $inactiveMember->full_name,
            'mobile' => $inactiveMember->mobile,
            'message_type' => 'Due Alert',
            'message_body' => 'Dues test message',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_monthly_broadcast_preview_excludes_inactive_members_and_event_beneficiaries()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_bc_test@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'KANYA-BC',
            'name' => 'Kanya Scheme BC',
            'name_hindi' => 'कन्या विवाह',
            'status' => 'Active'
        ]);

        $beneficiary = Member::create([
            'membership_no' => 'SHYAM-BEN-BC',
            'full_name' => 'कविता शर्मा',
            'father_spouse_name' => 'मोहन शर्मा',
            'gender' => 'Female',
            'dob' => '1998-01-01',
            'age' => 28,
            'mobile' => '9876500020',
            'scheme_id' => $scheme->id,
            'status' => 'Inactive',
            'joining_date' => now()->subMonths(10),
        ]);

        $activeMember = Member::create([
            'membership_no' => 'SHYAM-ACT-BC',
            'full_name' => 'दिनेश कुमार',
            'father_spouse_name' => 'सोहन कुमार',
            'gender' => 'Male',
            'dob' => '1994-01-01',
            'age' => 32,
            'mobile' => '9876500021',
            'scheme_id' => $scheme->id,
            'status' => 'Active',
            'joining_date' => now()->subMonths(10),
            'monthly_support_amount' => 200,
        ]);

        $event = MarriageEvent::create([
            'event_code' => 'EVT-BC-01',
            'title' => 'कविता विवाह कार्यक्रम',
            'event_type' => 'विवाह',
            'girl_name' => 'कविता शर्मा',
            'beneficiary_name' => 'कविता शर्मा',
            'member_id' => $beneficiary->id,
            'father_name' => 'मोहन शर्मा',
            'scheme_id' => $scheme->id,
            'event_date' => '2026-10-10',
            'venue' => 'लोहीकी',
            'target_amount' => 51000,
            'status' => 'Active',
        ]);

        // Fetch monthly events API preview
        $response = $this->actingAs($admin)->getJson(route('admin.api.events-by-month', ['month' => '2026-10']));
        $response->assertOk();

        $data = $response->json();
        $this->assertEquals(1, $data['events_count']);

        $previewMemberNames = collect($data['members_preview'])->pluck('name')->toArray();
        $this->assertContains('दिनेश कुमार', $previewMemberNames);
        $this->assertNotContains('कविता शर्मा', $previewMemberNames);
    }
}
