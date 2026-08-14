<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Member;
use App\Models\Nominee;
use App\Models\Payment;
use App\Models\MarriageEvent;
use App\Models\Payout;
use App\Models\SocietySetting;
use Carbon\Carbon;

class SocietyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Schemes
        $seniorScheme = Scheme::updateOrCreate(
            ['code' => 'SENIOR'],
            [
                'name' => 'Senior Welfare Scheme',
                'name_hindi' => 'बुजुर्ग सम्मान योजना',
                'type' => 'Senior Welfare Scheme',
                'status' => 'Active',
                'effective_from' => '2021-01-01',
                'effective_to' => '2030-12-31',
                'description' => 'Monthly financial support and welfare scheme for elderly society members.',
            ]
        );

        $marriageScheme = Scheme::updateOrCreate(
            ['code' => 'MARRIAGE'],
            [
                'name' => 'Marriage Scheme (Kanyadaan/Gotra)',
                'name_hindi' => 'विवाह (कन्यादान/गौत्र) योजना',
                'type' => 'Marriage Scheme',
                'status' => 'Active',
                'effective_from' => '2021-01-01',
                'effective_to' => '2030-12-31',
                'description' => 'Financial assistance scheme for girl child marriage and family welfare support.',
            ]
        );

        // 2. Age Slabs
        $seniorSlabs = [
            ['slab_code' => 'SLAB-S1', 'min_age' => 18, 'max_age' => 40, 'joining_amount' => 1100, 'support_amount' => 200],
            ['slab_code' => 'SLAB-S2', 'min_age' => 41, 'max_age' => 60, 'joining_amount' => 1500, 'support_amount' => 300],
            ['slab_code' => 'SLAB-S3', 'min_age' => 60, 'max_age' => 75, 'joining_amount' => 2000, 'support_amount' => 400],
            ['slab_code' => 'SLAB-S4', 'min_age' => 75, 'max_age' => 120, 'joining_amount' => 2500, 'support_amount' => 500],
        ];

        foreach ($seniorSlabs as $slab) {
            AgeSlab::updateOrCreate(
                ['scheme_id' => $seniorScheme->id, 'slab_code' => $slab['slab_code']],
                [
                    'min_age' => $slab['min_age'],
                    'max_age' => $slab['max_age'],
                    'joining_amount' => $slab['joining_amount'],
                    'support_amount' => $slab['support_amount'],
                    'status' => 'Active',
                    'effective_from' => '2021-01-01',
                    'effective_to' => '2030-12-31',
                ]
            );
        }

        $marriageSlabs = [
            ['slab_code' => 'SLAB-M1', 'min_age' => 0, 'max_age' => 5, 'joining_amount' => 1100, 'support_amount' => 100],
            ['slab_code' => 'SLAB-M2', 'min_age' => 6, 'max_age' => 9, 'joining_amount' => 1100, 'support_amount' => 200],
            ['slab_code' => 'SLAB-M3', 'min_age' => 10, 'max_age' => 13, 'joining_amount' => 2000, 'support_amount' => 300],
            ['slab_code' => 'SLAB-M4', 'min_age' => 14, 'max_age' => 17, 'joining_amount' => 2500, 'support_amount' => 400],
            ['slab_code' => 'SLAB-M5', 'min_age' => 17, 'max_age' => 120, 'joining_amount' => 2500, 'support_amount' => 500],
        ];

        foreach ($marriageSlabs as $slab) {
            AgeSlab::updateOrCreate(
                ['scheme_id' => $marriageScheme->id, 'slab_code' => $slab['slab_code']],
                [
                    'min_age' => $slab['min_age'],
                    'max_age' => $slab['max_age'],
                    'joining_amount' => $slab['joining_amount'],
                    'support_amount' => $slab['support_amount'],
                    'status' => 'Active',
                    'effective_from' => '2021-01-01',
                    'effective_to' => '2030-12-31',
                ]
            );
        }

        // 3. Agents
        $agentsData = [
            ['agent_code' => 'AGT-001', 'name' => 'Rameshwar Lal Sharma', 'code' => 'AGT01', 'mobile' => '9829012345', 'district' => 'Mahendragarh', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-002', 'name' => 'Suresh Kumar Yadav', 'code' => 'AGT02', 'mobile' => '9414023456', 'district' => 'Bhiwani', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-003', 'name' => 'Rajendra Prasad Verma', 'code' => 'AGT03', 'mobile' => '9812034567', 'district' => 'Rewari', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-004', 'name' => 'Sunita Devi Saini', 'code' => 'AGT04', 'mobile' => '9784045678', 'district' => 'Mahendragarh', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-005', 'name' => 'Mahesh Kumar Garg', 'code' => 'AGT05', 'mobile' => '9672056789', 'district' => 'Charkhi Dadri', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-006', 'name' => 'Virendra Singh Shekhawat', 'code' => 'AGT06', 'mobile' => '9828067890', 'district' => 'Jhunjhunu', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-007', 'name' => 'Mamta Sharma', 'code' => 'AGT07', 'mobile' => '9413078901', 'district' => 'Rewari', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-008', 'name' => 'Deepak Kumar Khandelwal', 'code' => 'AGT08', 'mobile' => '9829089012', 'district' => 'Mahendragarh', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-009', 'name' => 'Pawan Kumar Saini', 'code' => 'AGT09', 'mobile' => '9414090123', 'district' => 'Bhiwani', 'commission_rate' => 5.00],
            ['agent_code' => 'AGT-010', 'name' => 'Anil Kumar Yadav', 'code' => 'AGT10', 'mobile' => '9812001234', 'district' => 'Charkhi Dadri', 'commission_rate' => 5.00],
        ];

        $agentModels = [];
        foreach ($agentsData as $a) {
            $agentModels[] = Agent::updateOrCreate(
                ['agent_code' => $a['agent_code']],
                [
                    'name' => $a['name'],
                    'code' => $a['code'],
                    'mobile' => $a['mobile'],
                    'district' => $a['district'],
                    'commission_rate' => $a['commission_rate'],
                    'status' => 'Active',
                ]
            );
        }

        // 4. Members & Nominees
        $firstNamesM = ['Ramchandra', 'Satyanarayan', 'Ghanashyam', 'Bhagwan Das', 'Jagdish', 'Kishore', 'Omprakash', 'Banwari Lal', 'Goyal', 'Subhash', 'Tarachand', 'Mahavir', 'Dharamvir', 'Bhikam Chand', 'Harish', 'Nirmal', 'Sohan Lal', 'Prabhu Dayal', 'Radheshyam', 'Shriniwas', 'Vijay', 'Manoj', 'Mukesh', 'Rajesh', 'Sanjay', 'Sunil', 'Devender'];
        $firstNamesF = ['Shanti', 'Kamla', 'Rami', 'Bhagwati', 'Kausalya', 'Ganga', 'Geeta', 'Saraswati', 'Savitri', 'Laxmi', 'Parvati', 'Sita', 'Suman', 'Bimla', 'Sunita', 'Anita', 'Manju', 'Rekha', 'Prem', 'Santosh', 'Renu', 'Kavita', 'Pooja', 'Aarti', 'Kiran'];
        $lastNames = ['Sharma', 'Verma', 'Yadav', 'Saini', 'Gupta', 'Shekhawat', 'Khandelwal', 'Jangir', 'Agarwal', 'Choudhary', 'Rathore', 'Meena', 'Kanwar', 'Garg', 'Bhardwaj'];
        $gotras = ['Kaushik', 'Vats', 'Bhardwaj', 'Kashyap', 'Garg', 'Goyal', 'Bansal', 'Tanwar', 'Chauhan', 'Rathore', 'Dhillon', 'Sheoran'];
        $castes = ['Brahmin', 'Yadav', 'Saini', 'Mahajan', 'Rajput', 'Jat', 'Jangid'];

        $createdMembers = [];

        for ($i = 0; $i < 52; $i++) {
            $isSenior = $i < 34;
            $scheme = $isSenior ? $seniorScheme : $marriageScheme;
            $gender = ($i % 3 === 0) ? 'Female' : 'Male';
            $firstName = ($gender === 'Female') ? $firstNamesF[$i % count($firstNamesF)] : $firstNamesM[$i % count($firstNamesM)];
            $lastName = $lastNames[$i % count($lastNames)];
            $fullName = "{$firstName} {$lastName}";
            $fatherSpouse = ($gender === 'Female' && $i % 2 === 0) ? "W/o {$firstNamesM[($i + 5) % count($firstNamesM)]} {$lastName}" : "S/o {$firstNamesM[($i + 2) % count($firstNamesM)]} {$lastName}";
            $motherName = "{$firstNamesF[($i + 3) % count($firstNamesF)]} Devi";

            $dobYear = $isSenior ? (1945 + ($i % 35)) : (2008 + ($i % 16));
            $dobMonth = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            $dobDay = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
            $dob = "{$dobYear}-{$dobMonth}-{$dobDay}";
            $age = Carbon::parse($dob)->age;

            $slabs = $scheme->ageSlabs()->get();
            $matchedSlab = $slabs->first(function ($sl) use ($age) {
                return $age >= $sl->min_age && $age <= $sl->max_age;
            }) ?? $slabs->first();

            $agent = $agentModels[$i % count($agentModels)];
            $joiningDate = "2024-" . str_pad(($i % 8) + 1, 2, '0', STR_PAD_LEFT) . "-" . str_pad(($i % 25) + 1, 2, '0', STR_PAD_LEFT);
            $status = ($i === 48) ? 'Inactive' : (($i === 51) ? 'Suspended' : 'Active');
            $pendingAmount = ($status === 'Inactive') ? 1200 : (($i % 4 === 0) ? ($matchedSlab->support_amount * 2) : 0);
            $totalPaid = $matchedSlab->joining_amount + ($matchedSlab->support_amount * 6);

            $memNo = 'MEM-2026-' . (1001 + $i);

            $member = Member::updateOrCreate(
                ['membership_no' => $memNo],
                [
                    'full_name' => $fullName,
                    'father_spouse_name' => $fatherSpouse,
                    'mother_name' => $motherName,
                    'gender' => $gender,
                    'dob' => $dob,
                    'age' => $age,
                    'mobile' => '98' . rand(10000000, 99999999),
                    'gotra' => $gotras[$i % count($gotras)],
                    'caste' => $castes[$i % count($castes)],
                    'address' => 'Ward No. ' . (($i % 15) + 1) . ', Village Lohki, Tehsil Narnaul',
                    'district' => $agent->district,
                    'state' => 'Haryana',
                    'pincode' => '123001',
                    'aadhaar_no' => 'XXXX-XXXX-' . rand(1000, 9999),
                    'scheme_id' => $scheme->id,
                    'age_slab_id' => $matchedSlab ? $matchedSlab->id : null,
                    'joining_amount' => $matchedSlab ? $matchedSlab->joining_amount : 1100,
                    'monthly_support_amount' => $matchedSlab ? $matchedSlab->support_amount : 200,
                    'agent_id' => $agent->id,
                    'joining_date' => $joiningDate,
                    'status' => $status,
                    'pending_amount' => $pendingAmount,
                    'total_paid' => $totalPaid,
                ]
            );

            // Add 2 Nominees
            Nominee::updateOrCreate(
                ['member_id' => $member->id, 'priority' => 1],
                [
                    'name' => "{$firstNamesM[($i + 1) % count($firstNamesM)]} {$lastName}",
                    'relation' => ($gender === 'Female') ? 'Husband' : 'Son',
                    'mobile' => '98' . rand(10000000, 99999999),
                    'aadhaar_no' => 'XXXX-XXXX-' . rand(1000, 9999),
                ]
            );

            Nominee::updateOrCreate(
                ['member_id' => $member->id, 'priority' => 2],
                [
                    'name' => "{$firstNamesF[($i + 2) % count($firstNamesF)]} {$lastName}",
                    'relation' => ($gender === 'Female') ? 'Daughter' : 'Wife',
                    'mobile' => '98' . rand(10000000, 99999999),
                    'aadhaar_no' => 'XXXX-XXXX-' . rand(1000, 9999),
                ]
            );

            $createdMembers[] = $member;
        }

        // 5. Payments / Receipts
        $paymentModes = ['UPI', 'Cash', 'Bank Transfer', 'Cheque'];
        $receiptCount = 5001;

        foreach ($createdMembers as $idx => $m) {
            // Joining payment
            Payment::updateOrCreate(
                ['receipt_no' => 'REC-2026-' . $receiptCount],
                [
                    'san_code' => 'SAN-LOH-' . str_pad($m->id, 3, '0', STR_PAD_LEFT),
                    'member_id' => $m->id,
                    'agent_id' => $m->agent_id,
                    'amount' => $m->joining_amount,
                    'payment_type' => 'Joining Fee',
                    'payment_mode' => $paymentModes[$idx % count($paymentModes)],
                    'reference_no' => 'TXN' . rand(10000000, 99999999),
                    'month_year' => Carbon::parse($m->joining_date)->format('M Y'),
                    'payment_date' => $m->joining_date,
                    'status' => 'Verified',
                    'collected_by' => $m->agent ? $m->agent->name : 'HQ Admin',
                    'remarks' => 'Initial Membership Enrolment Fee',
                ]
            );
            $receiptCount++;

            // Monthly Support payment for first 15 members
            if ($idx < 15) {
                Payment::updateOrCreate(
                    ['receipt_no' => 'REC-2026-' . $receiptCount],
                    [
                        'san_code' => 'SAN-LOH-' . str_pad($m->id, 3, '0', STR_PAD_LEFT),
                        'member_id' => $m->id,
                        'agent_id' => $m->agent_id,
                        'amount' => $m->monthly_support_amount,
                        'payment_type' => 'Monthly Support',
                        'payment_mode' => $paymentModes[($idx + 1) % count($paymentModes)],
                        'reference_no' => 'UPI' . rand(10000000, 99999999),
                        'month_year' => 'Aug 2026',
                        'payment_date' => Carbon::now()->subDays($idx % 5)->format('Y-m-d'),
                        'status' => 'Verified',
                        'collected_by' => $m->agent ? $m->agent->name : 'HQ Admin',
                        'remarks' => 'Monthly welfare contribution for Aug 2026',
                    ]
                );
                $receiptCount++;
            }
        }

        // 6. Marriage Events
        $events = [
            [
                'event_code' => 'EVT-2026-01',
                'title' => 'Kumari Pooja Sharma Marriage Assistance',
                'girl_name' => 'Kumari Pooja Sharma',
                'father_name' => 'Radheshyam Sharma',
                'member_id' => $createdMembers[0]->id,
                'event_date' => '2026-09-12',
                'venue' => 'Shri Shyam Dharamshala, Lohki, Mahendragarh',
                'target_amount' => 51000,
                'collected_amount' => 46500,
                'beneficiary_payout_amount' => 51000,
                'status' => 'Upcoming',
                'description' => 'Official society marriage grant and member assistance for girl child marriage.',
            ],
            [
                'event_code' => 'EVT-2026-02',
                'title' => 'Kumari Rekha Saini Kanyadaan Assistance',
                'girl_name' => 'Kumari Rekha Saini',
                'father_name' => 'Kishore Saini',
                'member_id' => $createdMembers[1]->id,
                'event_date' => '2026-07-20',
                'venue' => 'Community Hall, Narnaul, Haryana',
                'target_amount' => 51000,
                'collected_amount' => 51000,
                'beneficiary_payout_amount' => 51000,
                'status' => 'Completed',
                'description' => 'Kanyadaan scheme support granted to member family.',
            ],
        ];

        foreach ($events as $e) {
            MarriageEvent::updateOrCreate(
                ['event_code' => $e['event_code']],
                $e
            );
        }

        // 7. Payouts
        Payout::updateOrCreate(
            ['payout_no' => 'PAY-2026-001'],
            [
                'event_id' => 2,
                'beneficiary_name' => 'Kishore Saini & Rekha Saini',
                'relation' => 'Father & Daughter',
                'amount' => 51000,
                'payout_date' => '2026-07-21',
                'approved_by' => 'Shri Navneet Sharma (Super Admin)',
                'payment_mode' => 'Bank Transfer',
                'transaction_ref' => 'NEFT-SBIN0001234-987654',
                'status' => 'Disbursed',
                'remarks' => 'Kanyadaan assistance grant disbursed directly to beneficiary account.',
            ]
        );

        // 8. Society Settings
        $settings = [
            ['key' => 'society_name_hi', 'value' => 'श्री श्याम वेलफेयर सोसायटी', 'group' => 'general', 'description' => 'Society Name in Hindi'],
            ['key' => 'society_name_en', 'value' => 'Shri Shyam Welfare Society', 'group' => 'general', 'description' => 'Society Name in English'],
            ['key' => 'society_address', 'value' => 'V.P.O. Lohki, Tehsil Narnaul, Distt. Mahendragarh, Haryana - 123001', 'group' => 'general', 'description' => 'Official Registered Address'],
            ['key' => 'society_reg_no', 'value' => 'HR/NNL/2021/04582', 'group' => 'general', 'description' => 'Government Registration Number'],
            ['key' => 'society_phone', 'value' => '+91 98290 12345', 'group' => 'general', 'description' => 'Head Office Phone'],
            ['key' => 'society_email', 'value' => 'contact@shrishyamwelfare.org', 'group' => 'general', 'description' => 'Head Office Email'],
            ['key' => 'receipt_san_prefix', 'value' => 'SAN-LOH-', 'group' => 'finance', 'description' => 'SAN Code Prefix'],
            ['key' => 'default_commission_rate', 'value' => '5', 'group' => 'finance', 'description' => 'Default Agent Commission Percentage'],
        ];

        foreach ($settings as $s) {
            SocietySetting::set($s['key'], $s['value'], $s['group'], $s['description']);
        }
    }
}
