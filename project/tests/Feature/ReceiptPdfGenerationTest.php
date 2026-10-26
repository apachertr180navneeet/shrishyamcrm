<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\Agent;
use App\Models\User;
use App\Models\Payment;
use App\Services\ReceiptService;

class ReceiptPdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_download_payment_receipt_pdf_with_accurate_hindi()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_receipt_test@shrishyamcrm.test',
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
            'agent_code' => 'AGT-REC-01',
            'name' => 'राम कुमार',
            'mobile' => '9664090906',
            'status' => 'Active',
            'district' => 'बालोतरा',
        ]);

        $member = Member::create([
            'membership_no' => 'MEM-2026-9001',
            'full_name' => 'सुरेश कुमार',
            'father_spouse_name' => 'मोहन लाल',
            'gender' => 'Male',
            'dob' => '1970-01-01',
            'age' => 56,
            'mobile' => '9876543210',
            'address' => 'बालोतरा',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'status' => 'Active',
            'joining_date' => '2026-01-01',
            'san_code' => 'SAN-BAL-001',
        ]);

        $payment = Payment::create([
            'payment_no' => 'PAY-REC-9001',
            'receipt_no' => 'REC-2026-9001',
            'member_id' => $member->id,
            'agent_id' => $agent->id,
            'scheme_id' => $scheme->id,
            'amount' => 1500.00,
            'payment_date' => '2026-10-26',
            'payment_type' => 'Monthly Support',
            'payment_mode' => 'UPI',
            'reference_no' => 'TXN998877',
            'status' => 'Verified',
            'amount_in_words' => 'One Thousand Five Hundred Rupees Only',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.payments.receipt.pdf', $payment->id));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('SSWS_Receipt_' . $payment->id . '.pdf', $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
