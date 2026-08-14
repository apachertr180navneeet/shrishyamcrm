<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Member;
use App\Services\MemberRegistrationService;
use Carbon\Carbon;

class MemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_member_registration_calculates_age_and_creates_records_in_transaction()
    {
        $seniorScheme = Scheme::create([
            'code' => 'SENIOR',
            'name' => 'Senior Welfare Scheme',
            'name_hindi' => 'बुजुर्ग सम्मान योजना',
            'status' => 'Active',
        ]);

        $slab = AgeSlab::create([
            'scheme_id' => $seniorScheme->id,
            'slab_code' => 'SLAB-S3',
            'min_age' => 60,
            'max_age' => 75,
            'joining_amount' => 2000,
            'support_amount' => 400,
            'status' => 'Active',
        ]);

        $agent = Agent::create([
            'agent_code' => 'AGT-001',
            'name' => 'Rameshwar Lal Sharma',
            'commission_rate' => 5.0,
            'status' => 'Active',
        ]);

        $dob = Carbon::now()->subYears(65)->format('Y-m-d'); // 65 years old -> SLAB-S3 (60-75)

        $member = MemberRegistrationService::register([
            'full_name' => 'Test Rameshwar Sharma',
            'father_spouse_name' => 'S/o Prabhu Dayal Sharma',
            'dob' => $dob,
            'gender' => 'Male',
            'mobile' => '9829099999',
            'district' => 'Mahendragarh',
            'state' => 'Haryana',
            'pincode' => '123001',
            'scheme_id' => $seniorScheme->id,
            'agent_id' => $agent->id,
            'nominee1_name' => 'Kiran Sharma',
            'nominee1_relation' => 'Spouse',
            'payment_mode' => 'UPI',
        ]);

        $this->assertNotNull($member->id);
        $this->assertEquals(65, $member->age);
        $this->assertStringStartsWith('MEM-', $member->membership_no);

        // Verify Age Slab (SLAB-S3: 60-75 yrs -> joining fee 2000, monthly support 400)
        $this->assertEquals(2000.0, (float)$member->joining_amount);
        $this->assertEquals(400.0, (float)$member->monthly_support_amount);

        // Verify Nominee created
        $this->assertDatabaseHas('nominees', [
            'member_id' => $member->id,
            'name' => 'Kiran Sharma',
            'relation' => 'Spouse'
        ]);

        // Verify Certificate created
        $this->assertDatabaseHas('certificates', [
            'member_id' => $member->id,
            'scheme_id' => $seniorScheme->id,
        ]);

        // Verify Ledger created
        $this->assertDatabaseHas('ledgers', [
            'member_id' => $member->id,
            'entry_type' => 'Joining Fee',
        ]);

        // Verify Initial Payment created
        $this->assertDatabaseHas('payments', [
            'member_id' => $member->id,
            'amount' => 2000.0,
            'payment_type' => 'Joining Fee',
        ]);
    }
}
