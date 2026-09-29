<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\User;

class MarriageEventCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_create_marriage_event_with_hindi_event_type()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_test@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'KANYA',
            'name' => 'Kanya Vivah Scheme',
            'name_hindi' => 'विवाह',
            'status' => 'Active'
        ]);

        $member = Member::create([
            'membership_no' => 'SHYAM-TEST-001',
            'full_name' => 'राकेश शर्मा',
            'father_spouse_name' => 'मदन लाल',
            'gender' => 'Male',
            'dob' => '1990-05-15',
            'age' => 36,
            'mobile' => '9876543210',
            'scheme_id' => $scheme->id,
            'status' => 'Active',
            'joining_date' => now()->subMonths(6),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Vivha Kirakam',
            'event_type' => 'विवाह',
            'girl_name' => 'पूजा',
            'beneficiary_name' => 'पूजा',
            'father_name' => 'राकेश शर्मा',
            'member_id' => $member->id,
            'scheme_id' => $scheme->id,
            'event_date' => '2026-10-22',
            'venue' => 'श्री श्याम धर्मशाला, लोहीकी',
            'target_amount' => 51000,
            'rate_per_event' => 200,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('marriage_events', [
            'title' => 'Vivha Kirakam',
            'event_type' => 'विवाह',
            'girl_name' => 'पूजा',
            'scheme_id' => $scheme->id,
        ]);
    }

    public function test_events_index_lists_only_members_not_nominees_in_beneficiaries_list()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_test2@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'KANYA2',
            'name' => 'Kanya Vivah Scheme 2',
            'name_hindi' => 'विवाह 2',
            'status' => 'Active'
        ]);

        $member = Member::create([
            'membership_no' => 'SHYAM-TEST-002',
            'full_name' => 'सुरेश शर्मा',
            'father_spouse_name' => 'राम लाल',
            'gender' => 'Male',
            'dob' => '1992-05-15',
            'age' => 34,
            'mobile' => '9876543211',
            'scheme_id' => $scheme->id,
            'status' => 'Active',
            'joining_date' => now()->subMonths(6),
        ]);

        // Create a nominee for member
        \App\Models\Nominee::create([
            'member_id' => $member->id,
            'name' => 'सुनीता शर्मा',
            'relation' => 'Spouse',
            'mobile' => '9876543212',
            'priority' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.index'));
        $response->assertOk();

        $beneficiariesList = $response->viewData('beneficiariesList');
        $this->assertNotEmpty($beneficiariesList);

        // Verify member is present
        $memberFound = $beneficiariesList->contains(fn($b) => $b['beneficiary_name'] === 'सुरेश शर्मा');
        $this->assertTrue($memberFound);

        // Verify nominee is NOT present
        $nomineeFound = $beneficiariesList->contains(fn($b) => $b['beneficiary_name'] === 'सुनीता शर्मा');
        $this->assertFalse($nomineeFound);
    }

    public function test_can_update_marriage_event()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_test3@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $scheme = Scheme::create([
            'code' => 'KANYA3',
            'name' => 'Kanya Vivah Scheme 3',
            'name_hindi' => 'कन्या विवाह',
            'status' => 'Active'
        ]);

        $member = Member::create([
            'membership_no' => 'SHYAM-TEST-003',
            'full_name' => 'दिनेश कुमार',
            'father_spouse_name' => 'हरि राम',
            'gender' => 'Male',
            'dob' => '1995-05-15',
            'age' => 31,
            'mobile' => '9876543213',
            'scheme_id' => $scheme->id,
            'status' => 'Active',
            'joining_date' => now()->subMonths(6),
        ]);

        $event = MarriageEvent::create([
            'event_code' => 'EVT-2026-99',
            'title' => 'Original Title',
            'event_type' => 'विवाह',
            'girl_name' => 'दिनेश कुमार',
            'father_name' => 'हरि राम',
            'member_id' => $member->id,
            'scheme_id' => $scheme->id,
            'event_date' => '2026-11-01',
            'venue' => 'स्थान 1',
            'target_amount' => 50000,
            'rate_per_event' => 200,
            'status' => 'Upcoming',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.events.update', $event->id), [
            'title' => 'Updated Title',
            'event_type' => 'कन्या विवाह',
            'beneficiary_name' => 'दिनेश कुमार',
            'father_name' => 'हरि राम शर्मा',
            'member_id' => $member->id,
            'scheme_id' => $scheme->id,
            'event_date' => '2026-11-05',
            'venue' => 'स्थान 2',
            'target_amount' => 60000,
            'rate_per_event' => 250,
            'status' => 'Active',
            'description' => 'Updated description notes',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('marriage_events', [
            'id' => $event->id,
            'title' => 'Updated Title',
            'father_name' => 'हरि राम शर्मा',
            'venue' => 'स्थान 2',
            'status' => 'Active',
        ]);
    }

    public function test_can_create_event_without_scheme_and_generates_contributions_for_all_active_members()
    {
        $this->withoutExceptionHandling();
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_test4@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $m1 = Member::create([
            'membership_no' => 'SHYAM-TEST-004',
            'full_name' => 'राकेश',
            'father_spouse_name' => 'मोहन लाल',
            'gender' => 'Male',
            'dob' => '1998-05-15',
            'age' => 28,
            'mobile' => '9876543214',
            'status' => 'Active',
            'joining_date' => now()->subMonths(6),
        ]);

        $m2 = Member::create([
            'membership_no' => 'SHYAM-TEST-005',
            'full_name' => 'सपना',
            'father_spouse_name' => 'सुरेश',
            'gender' => 'Female',
            'dob' => '2000-05-15',
            'age' => 26,
            'mobile' => '9876543215',
            'status' => 'Active',
            'joining_date' => now()->subMonths(6),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'beneficiary_name' => 'राकेश',
            'father_name' => 'मोहन लाल',
            'member_id' => $m1->id,
            'event_date' => '2026-10-30',
            'venue' => 'श्री श्याम धर्मशाला',
        ]);

        $response->assertSessionHasNoErrors();
        $event = MarriageEvent::where('girl_name', 'राकेश')->first();
        $this->assertNotNull($event);
        // Verify contributions generated for other active members, excluding beneficiary member
        $this->assertFalse($event->contributions()->where('member_id', $m1->id)->exists());
        $this->assertTrue($event->contributions()->where('member_id', $m2->id)->exists());

        // Verify member m1 membership status is automatically closed (Inactive)
        $m1->refresh();
        $this->assertEquals('Inactive', $m1->status);
    }

    public function test_member_membership_is_closed_when_marriage_event_is_created()
    {
        $admin = User::where('role', 'admin')->first() ?? User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_close_test@shrishyamcrm.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $member = Member::create([
            'membership_no' => 'SHYAM-TEST-CLOSE-01',
            'full_name' => 'कविता शर्मा',
            'father_spouse_name' => 'राजेश शर्मा',
            'gender' => 'Female',
            'dob' => '2001-08-10',
            'age' => 25,
            'mobile' => '9876543299',
            'status' => 'Active',
            'joining_date' => now()->subMonths(12),
        ]);

        $this->assertEquals('Active', $member->status);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'beneficiary_name' => 'कविता शर्मा',
            'father_name' => 'राजेश शर्मा',
            'member_id' => $member->id,
            'event_date' => '2026-11-15',
            'venue' => 'श्री श्याम धर्मशाला',
        ]);

        $response->assertSessionHasNoErrors();

        // Check member status has been closed
        $member->refresh();
        $this->assertEquals('Inactive', $member->status);
    }
}
