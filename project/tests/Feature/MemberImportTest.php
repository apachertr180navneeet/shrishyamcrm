<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class MemberImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Scheme $seniorScheme;
    protected AgeSlab $slab;
    protected Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::where('email', 'admin@shrishyam.org')->first() ?? User::factory()->create([
            'role' => 'admin',
        ]);

        $this->seniorScheme = Scheme::create([
            'code' => 'SENIOR_TEST',
            'name' => 'Senior Citizen Scheme',
            'name_hindi' => 'वरिष्ठ नागरिक योजना',
            'type' => 'Welfare',
            'status' => 'Active',
        ]);

        $this->slab = AgeSlab::create([
            'scheme_id' => $this->seniorScheme->id,
            'slab_code' => 'SLAB-TEST-1',
            'min_age' => 60,
            'max_age' => 75,
            'joining_amount' => 2100,
            'support_amount' => 350,
            'status' => 'Active',
        ]);

        $this->agent = Agent::create([
            'agent_code' => 'AGT-IMP-1',
            'name' => 'Import Test Agent',
            'commission_rate' => 5.0,
            'status' => 'Active',
        ]);
    }

    public function test_can_download_general_csv_template()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.members.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'Member_Import_Template_General.csv'));
    }

    public function test_can_download_scheme_specific_csv_template()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.members.template', ['scheme_id' => $this->seniorScheme->id]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'Member_Import_Template_SENIOR_TEST.csv'));
    }

    public function test_can_import_members_from_csv_with_selected_scheme()
    {
        $dob = Carbon::now()->subYears(65)->format('Y-m-d');
        
        $csvContent = implode(',', [
            'Full Name *', 'Mobile *', 'Date of Birth (YYYY-MM-DD) *', 'Gender', 'Father/Spouse Name', 'Mother Name',
            'Gotra', 'Caste', 'Address', 'District', 'State', 'Pincode', 'Aadhaar No', 'Scheme Code', 'Agent Code',
            'Joining Date', 'Nominee 1 Name', 'Nominee 1 Relation', 'Nominee 1 Mobile', 'Nominee 1 Share %'
        ]) . "\n";

        $csvContent .= implode(',', [
            'Radhey Shyam Verma', '9811122233', $dob, 'Male', 'S/o Mohan Lal', 'Kamla Devi',
            'Kaushik', 'General', 'Near Temple Lohidi', 'Balotra', 'Rajasthan', '344022', '444455556666',
            'SENIOR_TEST', 'AGT-IMP-1', Carbon::now()->format('Y-m-d'), 'Kamla Verma', 'Spouse', '9811122234', '100'
        ]) . "\n";

        $file = UploadedFile::fake()->createWithContent('members.csv', $csvContent);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.members.import'), [
                'scheme_id' => $this->seniorScheme->id,
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.members.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'full_name' => 'Radhey Shyam Verma',
            'mobile' => '9811122233',
            'scheme_id' => $this->seniorScheme->id,
            'agent_id' => $this->agent->id,
            'age' => 65,
        ]);

        $member = Member::where('mobile', '9811122233')->first();
        $this->assertNotNull($member);

        // Verify Nominee
        $this->assertDatabaseHas('nominees', [
            'member_id' => $member->id,
            'name' => 'Kamla Verma',
            'relation' => 'Spouse',
        ]);

        // Verify Certificate
        $this->assertDatabaseHas('certificates', [
            'member_id' => $member->id,
            'scheme_id' => $this->seniorScheme->id,
        ]);

        // Verify Ledger
        $this->assertDatabaseHas('ledgers', [
            'member_id' => $member->id,
            'entry_type' => 'Joining Fee',
        ]);
    }

    public function test_import_skips_invalid_rows_and_reports_errors()
    {
        $csvContent = "Full Name,Mobile,Date of Birth\n";
        $csvContent .= "Valid Member,9800000001,1960-01-01\n";
        $csvContent .= ",9800000002,1960-01-01\n"; // Missing full name
        $csvContent .= "Bad Phone,123,1960-01-01\n"; // Invalid mobile

        $file = UploadedFile::fake()->createWithContent('invalid_members.csv', $csvContent);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.members.import'), [
                'scheme_id' => $this->seniorScheme->id,
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.members.index'));
        $response->assertSessionHas('import_errors');

        $errors = session('import_errors');
        $this->assertCount(2, $errors);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Valid Member',
            'mobile' => '9800000001',
        ]);
    }
}
