<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarriageEvent;
use App\Models\Payout;
use App\Models\EventBilling;
use App\Models\WhatsAppLog;
use App\Models\SocietySetting;
use App\Models\Member;
use App\Models\Scheme;
use App\Services\NumberSeriesService;
use Carbon\Carbon;

class EventAndPayoutSeeder extends Seeder
{
    public function run(): void
    {
        $marriageScheme = Scheme::where('code', 'MARRIAGE')->first();
        $members = Member::where('status', 'Active')->get();

        // 1. Events
        $events = [
            [
                'event_code' => NumberSeriesService::getNextNumber('EVT', ['prefix' => 'EVT-2026-', 'initial_value' => 1, 'padding' => 2]),
                'title' => 'Kumari Pooja Marriage Assistance Grant',
                'event_type' => 'Marriage Support',
                'girl_name' => 'Kumari Pooja Sharma',
                'father_name' => 'Radheshyam Sharma',
                'event_date' => '2026-08-20',
                'venue' => 'Shri Shyam Dharamshala, Lohki',
                'target_amount' => 51000.0,
                'collected_amount' => 51000.0,
                'beneficiary_payout_amount' => 51000.0,
                'rate_per_event' => 200.0,
                'status' => 'Upcoming',
                'description' => 'Society marriage assistance grant disbursed for the wedding of sister Pooja.',
            ],
            [
                'event_code' => NumberSeriesService::getNextNumber('EVT', ['prefix' => 'EVT-2026-', 'initial_value' => 1, 'padding' => 2]),
                'title' => 'Kumari Aarti Kanyadaan Welfare Grant',
                'event_type' => 'Marriage Support',
                'girl_name' => 'Kumari Aarti Yadav',
                'father_name' => 'Suresh Kumar Yadav',
                'event_date' => '2026-09-05',
                'venue' => 'Community Hall, Ateli Mandi',
                'target_amount' => 51000.0,
                'collected_amount' => 51000.0,
                'beneficiary_payout_amount' => 51000.0,
                'rate_per_event' => 200.0,
                'status' => 'Upcoming',
                'description' => 'Financial assistance support under Marriage Welfare Scheme.',
            ],
            [
                'event_code' => NumberSeriesService::getNextNumber('EVT', ['prefix' => 'EVT-2026-', 'initial_value' => 1, 'padding' => 2]),
                'title' => 'Kumari Rekha Marriage Welfare Grant',
                'event_type' => 'Marriage Support',
                'girl_name' => 'Kumari Rekha Saini',
                'father_name' => 'Subhash Chand Saini',
                'event_date' => '2026-06-18',
                'venue' => 'Main Bazar, Lohki',
                'target_amount' => 51000.0,
                'collected_amount' => 51000.0,
                'beneficiary_payout_amount' => 51000.0,
                'rate_per_event' => 200.0,
                'status' => 'Completed',
                'description' => 'Completed marriage grant disbursement.',
            ],
        ];

        $createdEvents = [];
        foreach ($events as $ev) {
            $createdEvents[] = MarriageEvent::create(
                array_merge($ev, ['scheme_id' => $marriageScheme?->id])
            );
        }

        // 2. Beneficiary Payouts
        $payouts = [
            [
                'payout_no' => NumberSeriesService::getNextNumber('PAY', ['prefix' => 'PAY-2026-', 'initial_value' => 1, 'padding' => 3]),
                'event_id' => $createdEvents[2]->id,
                'beneficiary_name' => 'Subhash Chand Saini (Father)',
                'relation' => 'Father',
                'payout_type' => 'Marriage Assistance',
                'amount' => 51000.0,
                'payout_date' => '2026-06-15',
                'approved_by' => 'Shri Navneet Sharma (President)',
                'disbursed_by' => 'Mahesh Kumar Garg (Treasurer)',
                'payment_mode' => 'Bank Transfer',
                'transaction_ref' => 'UTR9823481239',
                'status' => 'Disbursed',
                'remarks' => 'Full marriage grant disbursed via NEFT/RTGS to beneficiary bank account.',
            ],
            [
                'payout_no' => NumberSeriesService::getNextNumber('PAY', ['prefix' => 'PAY-2026-', 'initial_value' => 1, 'padding' => 3]),
                'event_id' => $createdEvents[0]->id,
                'beneficiary_name' => 'Radheshyam Sharma (Father)',
                'relation' => 'Father',
                'payout_type' => 'Marriage Assistance',
                'amount' => 51000.0,
                'payout_date' => '2026-08-14',
                'approved_by' => 'Shri Navneet Sharma (President)',
                'disbursed_by' => null,
                'payment_mode' => 'Cheque',
                'transaction_ref' => 'CHQ-829104',
                'status' => 'Approved',
                'remarks' => 'Approved by society committee, awaiting disbursement on event date.',
            ],
        ];

        foreach ($payouts as $p) {
            Payout::create($p);
        }

        // 3. Consolidated Event Billings History
        EventBilling::create([
            'event_id' => $createdEvents[2]->id,
            'billing_month' => '2026-06',
            'month_name' => 'June 2026',
            'scheme_id' => $marriageScheme?->id,
            'events_count' => 1,
            'rate_per_event' => 200.0,
            'total_per_member' => 200.0,
            'billed_members_count' => $members->count(),
            'total_billing_amount' => $members->count() * 200.0,
            'billing_date' => '2026-06-01',
        ]);

        // 4. WhatsApp Communication Logs
        $sampleMember = $members->first();
        if ($sampleMember) {
            WhatsAppLog::create([
                'member_id' => $sampleMember->id,
                'recipient_name' => $sampleMember->full_name,
                'mobile' => $sampleMember->mobile,
                'message_type' => 'Receipt',
                'message_body' => "श्री श्याम वेलफेयर सोसायटी लोहीकी - रसीद सं: REC-2026-5001 प्राप्त हुई।",
                'status' => 'Sent',
                'sent_at' => Carbon::now()->subDays(2),
            ]);
        }

        // 5. Society Settings
        $settings = [
            'society_name' => 'Shri Shyam Welfare Society',
            'society_name_hindi' => 'श्री श्याम वेलफेयर सोसायटी लोहीकी',
            'reg_no' => 'HR/019/2021/04582',
            'san_prefix' => 'SAN-LOH',
            'address' => 'Main Bazar, Lohki, District Mahendragarh, Haryana - 123001',
            'phone' => '+91 98290 12345',
            'email' => 'info@shrishyamwelfare.org',
            'president_name' => 'Shri Navneet Sharma',
            'secretary_name' => 'Shri Mahesh Garg',
            'treasurer_name' => 'Shri Rameshwar Lal',
            'default_event_rate' => '200',
            'default_commission' => '5',
        ];

        foreach ($settings as $key => $val) {
            SocietySetting::setVal($key, $val);
        }
    }
}
