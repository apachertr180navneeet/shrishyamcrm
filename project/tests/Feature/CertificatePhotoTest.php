<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\Agent;

class CertificatePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_certificate_web_view_displays_member_photo()
    {
        $admin = User::where('role', 'admin')->first();

        $agent = Agent::create([
            'agent_code' => 'AGT-CERT-1',
            'name' => 'Agent Certificate',
            'commission_rate' => 5.0,
            'status' => 'Active',
            'district' => 'Jaipur',
            'mobile' => '9800000010',
        ]);

        $scheme = Scheme::create([
            'code' => 'SCHEME-CERT-1',
            'name' => 'Kanyadaan Vivah Scheme',
            'name_hindi' => 'कन्यादान विवाह योजना',
            'status' => 'Active',
        ]);

        // Upload fake photo file
        $file = UploadedFile::fake()->image('test_member_pic.jpg', 300, 300);
        $path = $file->storeAs('uploads/documents', 'test_member_pic.jpg', 'public');

        $member = Member::create([
            'membership_no' => 'MEM-CERT-001',
            'full_name' => 'Pooja Sharma',
            'mobile' => '9829000111',
            'gender' => 'Female',
            'dob' => '2000-01-01',
            'status' => 'Active',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'photo' => '/storage/' . $path,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.show', $member->id));

        $response->assertStatus(200);
        $response->assertSee('/storage/uploads/documents/test_member_pic.jpg');
        $response->assertSee('Pooja Sharma');
    }

    public function test_certificate_pdf_downloads_successfully_with_photo()
    {
        $admin = User::where('role', 'admin')->first();

        $agent = Agent::create([
            'agent_code' => 'AGT-CERT-2',
            'name' => 'Agent Certificate 2',
            'commission_rate' => 5.0,
            'status' => 'Active',
            'district' => 'Sikar',
            'mobile' => '9800000020',
        ]);

        $scheme = Scheme::create([
            'code' => 'SCHEME-CERT-2',
            'name' => 'Kanyadaan Vivah Scheme 2',
            'name_hindi' => 'कन्यादान विवाह योजना २',
            'status' => 'Active',
        ]);

        $file = UploadedFile::fake()->image('cert_pdf_pic.jpg', 300, 300);
        $path = $file->storeAs('uploads/documents', 'cert_pdf_pic.jpg', 'public');

        $member = Member::create([
            'membership_no' => 'MEM-CERT-002',
            'full_name' => 'Rekha Devi',
            'mobile' => '9829000222',
            'gender' => 'Female',
            'dob' => '1998-05-10',
            'status' => 'Active',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'photo' => '/storage/' . $path,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.pdf', $member->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_certificate_with_db_base64_photo()
    {
        $admin = User::where('role', 'admin')->first();

        $agent = Agent::create([
            'agent_code' => 'AGT-CERT-3',
            'name' => 'Agent Certificate 3',
            'commission_rate' => 5.0,
            'status' => 'Active',
            'district' => 'Alwar',
            'mobile' => '9800000030',
        ]);

        $scheme = Scheme::create([
            'code' => 'SCHEME-CERT-3',
            'name' => 'Kanyadaan Vivah Scheme 3',
            'name_hindi' => 'कन्यादान विवाह योजना ३',
            'status' => 'Active',
        ]);

        // 1x1 transparent PNG as base64
        $base64Photo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $member = Member::create([
            'membership_no' => 'MEM-CERT-003',
            'full_name' => 'Kavita Kumari',
            'mobile' => '9829000333',
            'gender' => 'Female',
            'dob' => '2001-08-20',
            'status' => 'Active',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'photo' => $base64Photo,
        ]);

        // Verify web certificate renders with DB stored base64 image
        $response = $this->actingAs($admin)->get(route('admin.certificates.show', $member->id));
        $response->assertStatus(200);
        $response->assertSee('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', false);
        $response->assertSee('Kavita Kumari');

        // Verify PDF certificate compiles successfully with base64 image
        $pdfResponse = $this->actingAs($admin)->get(route('admin.certificates.pdf', $member->id));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_certificate_with_full_image_url_in_db()
    {
        $admin = User::where('role', 'admin')->first();

        $agent = Agent::create([
            'agent_code' => 'AGT-CERT-4',
            'name' => 'Agent Certificate 4',
            'commission_rate' => 5.0,
            'status' => 'Active',
            'district' => 'Bikaner',
            'mobile' => '9800000040',
        ]);

        $scheme = Scheme::create([
            'code' => 'SCHEME-CERT-4',
            'name' => 'Kanyadaan Vivah Scheme 4',
            'name_hindi' => 'कन्यादान विवाह योजना ४',
            'status' => 'Active',
        ]);

        $file = UploadedFile::fake()->image('full_url_pic.jpg', 300, 300);
        $path = $file->storeAs('uploads/documents', 'full_url_pic.jpg', 'public');
        $fullUrl = asset('storage/' . $path);

        $member = Member::create([
            'membership_no' => 'MEM-CERT-004',
            'full_name' => 'Suman Devi',
            'mobile' => '9829000444',
            'gender' => 'Female',
            'dob' => '1999-03-25',
            'status' => 'Active',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'photo' => $fullUrl,
        ]);

        // Verify web certificate renders full image URL from DB
        $response = $this->actingAs($admin)->get(route('admin.certificates.show', $member->id));
        $response->assertStatus(200);
        $response->assertSee($fullUrl, false);
        $response->assertSee('Suman Devi');

        // Verify PDF certificate compiles successfully with full URL from DB
        $pdfResponse = $this->actingAs($admin)->get(route('admin.certificates.pdf', $member->id));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}
