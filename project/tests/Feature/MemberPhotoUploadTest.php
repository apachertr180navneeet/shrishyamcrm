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
use App\Models\AgeSlab;

class MemberPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_can_enroll_member_with_photo()
    {
        $admin = User::where('role', 'admin')->first();

        $agent = Agent::create([
            'agent_code' => 'AGT-PHOTO-1',
            'name' => 'Agent Photo Test',
            'commission_rate' => 5.0,
            'status' => 'Active',
            'district' => 'Jaipur',
            'mobile' => '9800000010',
        ]);

        $scheme = Scheme::create([
            'code' => 'SCHEME-PHOTO-1',
            'name' => 'General Welfare Scheme',
            'name_hindi' => 'कल्याण योजना',
            'status' => 'Active',
        ]);

        $photoFile = UploadedFile::fake()->image('member_photo.jpg', 300, 300);

        $response = $this->actingAs($admin)->post(route('admin.members.store'), [
            'full_name' => 'Sohan Lal',
            'mobile' => '9829911223',
            'gender' => 'Male',
            'dob' => '1995-05-15',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'photo' => $photoFile,
        ]);

        $response->assertSessionHasNoErrors();
        $member = Member::where('mobile', '9829911223')->first();
        $this->assertNotNull($member);
        $this->assertNotNull($member->photo);
        // Verify full image URL is stored directly in database
        $this->assertStringStartsWith('http', $member->photo);
        $this->assertStringContainsString('/storage/uploads/documents/', $member->photo);
        $this->assertEquals($member->photo, $member->photo_src);

        // Verify document is also stored in member_documents
        $doc = $member->documents()->where('document_type', 'Photo')->first();
        $this->assertNotNull($doc);
        $storedPath = str_replace('/storage/', '', $doc->file_path);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_can_update_member_photo_in_edit()
    {
        $admin = User::where('role', 'admin')->first();

        $agent = Agent::create([
            'agent_code' => 'AGT-PHOTO-2',
            'name' => 'Agent Photo Two',
            'commission_rate' => 5.0,
            'status' => 'Active',
            'district' => 'Sikar',
            'mobile' => '9800000020',
        ]);

        $scheme = Scheme::create([
            'code' => 'SCHEME-PHOTO-2',
            'name' => 'General Welfare Scheme Two',
            'name_hindi' => 'कल्याण योजना २',
            'status' => 'Active',
        ]);

        $member = Member::create([
            'membership_no' => 'MEM-TEST-PHOTO-1',
            'full_name' => 'Original Name',
            'mobile' => '9876500001',
            'gender' => 'Male',
            'dob' => '1990-01-01',
            'status' => 'Active',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
        ]);

        $newPhoto = UploadedFile::fake()->image('updated_photo.png', 400, 400);

        $response = $this->actingAs($admin)->put(route('admin.members.update', $member->id), [
            'full_name' => 'Updated Name',
            'mobile' => '9876500001',
            'gender' => 'Male',
            'dob' => '1990-01-01',
            'status' => 'Active',
            'scheme_id' => $scheme->id,
            'agent_id' => $agent->id,
            'photo' => $newPhoto,
        ]);

        $response->assertRedirect(route('admin.members.show', $member->id));
        $member->refresh();

        $this->assertEquals('Updated Name', $member->full_name);
        $this->assertNotNull($member->photo);
        $this->assertStringStartsWith('http', $member->photo);
        $this->assertStringContainsString('/storage/uploads/documents/', $member->photo);
        $this->assertEquals($member->photo, $member->photo_src);

        $doc = $member->documents()->where('document_type', 'Photo')->first();
        $this->assertNotNull($doc);
        $storedPath = str_replace('/storage/', '', $doc->file_path);
        Storage::disk('public')->assertExists($storedPath);
    }
}
