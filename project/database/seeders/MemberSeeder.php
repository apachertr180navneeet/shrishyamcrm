<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Nominee;
use App\Models\Payment;
use App\Models\Ledger;
use App\Models\Certificate;
use App\Models\AgentCommission;
use App\Services\NumberSeriesService;
use Carbon\Carbon;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $seniorScheme = Scheme::where('code', 'SENIOR')->first();
        $marriageScheme = Scheme::where('code', 'MARRIAGE')->first();
        $agents = Agent::all();

        if ($agents->isEmpty()) {
            $this->command?->warn('No agents found. Skipping member seeding.');
            return;
        }

        $firstNamesM = ['Ramchandra', 'Satyanarayan', 'Ghanashyam', 'Bhagwan Das', 'Jagdish', 'Kishore', 'Omprakash', 'Banwari Lal', 'Goyal', 'Subhash', 'Tarachand', 'Mahavir', 'Dharamvir', 'Bhikam Chand', 'Harish', 'Nirmal', 'Sohan Lal', 'Prabhu Dayal', 'Radheshyam', 'Shriniwas', 'Vijay', 'Manoj', 'Mukesh', 'Rajesh', 'Sanjay', 'Sunil', 'Devender'];
        $firstNamesF = ['Shanti', 'Kamla', 'Rami', 'Bhagwati', 'Kausalya', 'Ganga', 'Geeta', 'Saraswati', 'Savitri', 'Laxmi', 'Parvati', 'Sita', 'Suman', 'Bimla', 'Sunita', 'Anita', 'Manju', 'Rekha', 'Prem', 'Santosh', 'Renu', 'Kavita', 'Pooja', 'Aarti', 'Kiran'];
        $lastNames = ['Sharma', 'Verma', 'Yadav', 'Saini', 'Gupta', 'Shekhawat', 'Khandelwal', 'Jangir', 'Agarwal', 'Choudhary', 'Rathore', 'Meena', 'Kanwar', 'Garg', 'Bhardwaj'];
        $gotras = ['Kaushik', 'Vats', 'Bhardwaj', 'Kashyap', 'Garg', 'Goyal', 'Bansal', 'Tanwar', 'Chauhan', 'Rathore', 'Dhillon', 'Sheoran'];
        $castes = ['Brahmin', 'Yadav', 'Saini', 'Mahajan', 'Rajput', 'Jat', 'Jangid'];

        for ($i = 0; $i < 52; $i++) {
            $isSenior = $i < 34;
            $scheme = $isSenior ? $seniorScheme : $marriageScheme;

            $gender = ($i % 3 === 0) ? 'Female' : 'Male';
            $firstName = ($gender === 'Female')
                ? $firstNamesF[$i % count($firstNamesF)]
                : $firstNamesM[$i % count($firstNamesM)];
            $lastName = $lastNames[$i % count($lastNames)];
            $name = "{$firstName} {$lastName}";
            $fatherName = ($gender === 'Female' && $i % 2 === 0) 
                ? 'W/o ' . $firstNamesM[($i + 5) % count($firstNamesM)] . ' ' . $lastName 
                : 'S/o ' . $firstNamesM[($i + 2) % count($firstNamesM)] . ' ' . $lastName;
            $motherName = $firstNamesF[($i + 3) % count($firstNamesF)] . ' Devi';

            $dobYear = $isSenior ? (1945 + ($i % 35)) : (2008 + ($i % 16));
            $dobMonth = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            $dobDay = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
            $dob = Carbon::parse("{$dobYear}-{$dobMonth}-{$dobDay}");
            $age = $dob->age;

            $slab = AgeSlab::where('scheme_id', $scheme->id)
                ->where('min_age', '<=', $age)
                ->where('max_age', '>=', $age)
                ->first() ?: AgeSlab::where('scheme_id', $scheme->id)->first();

            $agent = $agents[$i % $agents->count()];
            $joiningDate = Carbon::parse("2024-" . str_pad(($i % 8) + 1, 2, '0', STR_PAD_LEFT) . "-" . str_pad(($i % 25) + 1, 2, '0', STR_PAD_LEFT));

            $status = 'Active';
            if ($i === 5 || $i === 18 || $i === 31) $status = 'Inactive';
            if ($i === 49) $status = 'Suspended';

            $memNo = NumberSeriesService::getNextNumber('MEM', ['prefix' => 'MEM-2026-', 'initial_value' => 1001, 'padding' => 4]);
            $joiningAmount = $slab ? (float)$slab->joining_amount : 1100.0;
            $monthlySupport = $slab ? (float)$slab->support_amount : 200.0;

            // Compute paid and pending
            $monthsActive = ($i % 6) + 1;
            $totalDues = $joiningAmount + ($monthlySupport * $monthsActive) + ($i % 3 === 0 ? 800 : 0);
            $totalPaid = $joiningAmount + ($monthlySupport * ($monthsActive - ($i % 4 === 0 ? 2 : 0)));
            $pendingAmount = max(0, $totalDues - $totalPaid);

            $member = Member::create([
                'membership_no' => $memNo,
                'full_name' => $name,
                'father_spouse_name' => $fatherName,
                'mother_name' => $motherName,
                'gender' => $gender,
                'dob' => $dob->toDateString(),
                'age' => $age,
                'mobile' => '98' . str_pad(10000000 + ($i * 123456), 8, '0', STR_PAD_LEFT),
                'gotra' => $gotras[$i % count($gotras)],
                'caste' => $castes[$i % count($castes)],
                'address' => 'House No. ' . ($i + 12) . ', Main Bazar, Lohki',
                'district' => $agent->district,
                'state' => 'Haryana',
                'pincode' => '123001',
                'aadhaar_no' => '4829 ' . (1000 + $i * 12) . ' ' . (2000 + $i * 34),
                'scheme_id' => $scheme->id,
                'age_slab_id' => $slab?->id,
                'joining_amount' => $joiningAmount,
                'monthly_support_amount' => $monthlySupport,
                'agent_id' => $agent->id,
                'joining_date' => $joiningDate->toDateString(),
                'status' => $status,
                'pending_amount' => $pendingAmount,
                'total_paid' => $totalPaid,
            ]);

            // Nominee 1
            Nominee::create([
                'member_id' => $member->id,
                'priority' => 1,
                'name' => $firstNamesM[($i + 1) % count($firstNamesM)] . ' ' . $lastName,
                'father_husband_name' => $fatherName,
                'relation' => $isSenior ? 'Son' : 'Father',
                'mobile' => '98' . str_pad(30000000 + ($i * 333333), 8, '0', STR_PAD_LEFT),
                'aadhaar_no' => '3920 ' . (2000 + $i * 15) . ' ' . (3000 + $i * 22),
                'address' => $member->address,
                'percentage' => 100.0,
            ]);

            // Nominee 2
            Nominee::create([
                'member_id' => $member->id,
                'priority' => 2,
                'name' => $firstNamesF[($i + 4) % count($firstNamesF)] . ' ' . $lastName,
                'father_husband_name' => $fatherName,
                'relation' => $isSenior ? 'Daughter-in-Law' : 'Mother',
                'mobile' => '94' . str_pad(40000000 + ($i * 444444), 8, '0', STR_PAD_LEFT),
                'aadhaar_no' => '7712 ' . (4000 + $i * 18) . ' ' . (5000 + $i * 11),
                'address' => $member->address,
                'percentage' => 50.0,
            ]);

            // Certificate
            $certNo = NumberSeriesService::getNextNumber('CRT', ['prefix' => 'CRT-2026-', 'initial_value' => 8001, 'padding' => 4]);
            Certificate::create([
                'certificate_no' => $certNo,
                'member_id' => $member->id,
                'scheme_id' => $scheme->id,
                'issue_date' => $joiningDate->toDateString(),
                'authorized_by' => 'President / General Secretary',
                'verification_code' => strtoupper(substr(md5($certNo . $member->id), 0, 10)),
            ]);

            // 1. Initial Joining Due Ledger
            $txn1 = NumberSeriesService::getNextNumber('TXN', ['prefix' => 'TXN-2026-', 'initial_value' => 10001, 'padding' => 5]);
            Ledger::create([
                'transaction_no' => $txn1,
                'member_id' => $member->id,
                'agent_id' => $agent->id,
                'transaction_date' => $joiningDate->toDateString(),
                'entry_type' => 'Joining Fee',
                'description' => 'Initial Membership Enrollment Fee Due',
                'debit' => $joiningAmount,
                'credit' => 0,
                'running_balance' => $joiningAmount,
            ]);

            // 2. Initial Payment
            $recNo = NumberSeriesService::getNextNumber('REC', ['prefix' => 'REC-2026-', 'initial_value' => 5001, 'padding' => 4]);
            $payment = Payment::create([
                'receipt_no' => $recNo,
                'san_code' => 'SAN-LOH-' . str_pad($member->id, 3, '0', STR_PAD_LEFT),
                'member_id' => $member->id,
                'agent_id' => $agent->id,
                'amount' => $joiningAmount,
                'payment_type' => 'Joining Fee',
                'payment_mode' => ($i % 2 === 0) ? 'UPI' : 'Cash',
                'reference_no' => ($i % 2 === 0) ? 'TXN' . (9823400 + $i) : 'CASH-' . rand(1000, 9999),
                'month_year' => $joiningDate->format('M Y'),
                'payment_date' => $joiningDate->toDateString(),
                'status' => 'Verified',
                'collected_by' => $agent->name,
                'remarks' => 'Initial enrollment joining fee',
            ]);

            // 3. Payment Credit Ledger
            $txn2 = NumberSeriesService::getNextNumber('TXN', ['prefix' => 'TXN-2026-', 'initial_value' => 10001, 'padding' => 5]);
            Ledger::create([
                'transaction_no' => $txn2,
                'member_id' => $member->id,
                'agent_id' => $agent->id,
                'payment_id' => $payment->id,
                'transaction_date' => $joiningDate->toDateString(),
                'entry_type' => 'Payment',
                'description' => "Initial Enrollment Fee Paid - Receipt #{$recNo}",
                'debit' => 0,
                'credit' => $joiningAmount,
                'running_balance' => 0,
            ]);

            // Agent Commission on joining fee
            AgentCommission::create([
                'agent_id' => $agent->id,
                'payment_id' => $payment->id,
                'member_id' => $member->id,
                'collection_amount' => $joiningAmount,
                'commission_rate' => $agent->commission_rate,
                'commission_amount' => round($joiningAmount * ($agent->commission_rate / 100), 2),
                'status' => 'Earned',
            ]);

            // Additional monthly payment if active
            if ($status === 'Active') {
                $monthlyPayDate = Carbon::parse('2026-07-05');
                $monthlyRecNo = NumberSeriesService::getNextNumber('REC', ['prefix' => 'REC-2026-', 'initial_value' => 5001, 'padding' => 4]);

                // Monthly due
                $txn3 = NumberSeriesService::getNextNumber('TXN', ['prefix' => 'TXN-2026-', 'initial_value' => 10001, 'padding' => 5]);
                Ledger::create([
                    'transaction_no' => $txn3,
                    'member_id' => $member->id,
                    'agent_id' => $agent->id,
                    'transaction_date' => '2026-07-01',
                    'entry_type' => 'Monthly Due',
                    'description' => 'July 2026 Monthly Scheme Contribution Due',
                    'debit' => $monthlySupport,
                    'credit' => 0,
                    'running_balance' => $monthlySupport,
                ]);

                // If not overdue, record monthly payment
                if ($pendingAmount == 0) {
                    $p2 = Payment::create([
                        'receipt_no' => $monthlyRecNo,
                        'san_code' => 'SAN-LOH-' . str_pad($member->id, 3, '0', STR_PAD_LEFT),
                        'member_id' => $member->id,
                        'agent_id' => $agent->id,
                        'amount' => $monthlySupport,
                        'payment_type' => 'Monthly Support',
                        'payment_mode' => 'UPI',
                        'reference_no' => 'TXN' . rand(1000000, 9999999),
                        'month_year' => 'Jul 2026',
                        'payment_date' => $monthlyPayDate->toDateString(),
                        'status' => 'Verified',
                        'collected_by' => $agent->name,
                        'remarks' => 'July monthly contribution',
                    ]);

                    $txn4 = NumberSeriesService::getNextNumber('TXN', ['prefix' => 'TXN-2026-', 'initial_value' => 10001, 'padding' => 5]);
                    Ledger::create([
                        'transaction_no' => $txn4,
                        'member_id' => $member->id,
                        'agent_id' => $agent->id,
                        'payment_id' => $p2->id,
                        'transaction_date' => $monthlyPayDate->toDateString(),
                        'entry_type' => 'Payment',
                        'description' => "July 2026 Contribution Paid - Receipt #{$monthlyRecNo}",
                        'debit' => 0,
                        'credit' => $monthlySupport,
                        'running_balance' => 0,
                    ]);

                    AgentCommission::create([
                        'agent_id' => $agent->id,
                        'payment_id' => $p2->id,
                        'member_id' => $member->id,
                        'collection_amount' => $monthlySupport,
                        'commission_rate' => $agent->commission_rate,
                        'commission_amount' => round($monthlySupport * ($agent->commission_rate / 100), 2),
                        'status' => 'Earned',
                    ]);
                }
            }
        }
    }
}
