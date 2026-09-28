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
}
