<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Nominee;
use App\Models\MemberDocument;
use App\Models\Certificate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MemberRegistrationService
{
    /**
     * Complete 5-step member registration in a single atomic database transaction.
     */
    public static function register(array $data, ?array $files = null): Member
    {
        return DB::transaction(function () use ($data, $files) {
            // 1. Calculate Age from DOB
            $dob = Carbon::parse($data['dob']);
            $age = $dob->age;

            // 2. Resolve Scheme & Applicable Age Slab
            $scheme = Scheme::findOrFail($data['scheme_id']);
            $slab = AgeSlab::where('scheme_id', $scheme->id)
                ->where('status', 'Active')
                ->where('min_age', '<=', $age)
                ->where('max_age', '>=', $age)
                ->first();

            if (!$slab) {
                $slab = AgeSlab::where('scheme_id', $scheme->id)->first();
            }

            $joiningAmount = isset($data['joining_amount']) && $data['joining_amount'] !== '' 
                ? (float)$data['joining_amount'] 
                : ($slab ? (float)$slab->joining_amount : 1100.0);

            $monthlySupport = isset($data['monthly_support_amount']) && $data['monthly_support_amount'] !== ''
                ? (float)$data['monthly_support_amount']
                : ($slab ? (float)$slab->support_amount : 200.0);

            // 3. Generate Sequential Membership Number
            if (empty($data['membership_no']) || Member::where('membership_no', $data['membership_no'])->exists()) {
                $memNo = NumberSeriesService::getNextNumber('MEM', ['prefix' => 'MEM-' . Carbon::now()->format('Y') . '-', 'initial_value' => 1001, 'padding' => 4]);
            } else {
                $memNo = $data['membership_no'];
                NumberSeriesService::getNextNumber('MEM', ['prefix' => 'MEM-' . Carbon::now()->format('Y') . '-', 'initial_value' => 1001, 'padding' => 4]);
            }

            $joiningDate = $data['joining_date'] ?? now()->toDateString();

            // 4. Create Member Entity
            $member = Member::create([
                'membership_no' => $memNo,
                'full_name' => $data['full_name'],
                'father_spouse_name' => $data['father_spouse_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'gender' => $data['gender'] ?? 'Male',
                'dob' => $dob->toDateString(),
                'age' => $age,
                'mobile' => $data['mobile'],
                'gotra' => $data['gotra'] ?? null,
                'caste' => $data['caste'] ?? null,
                'address' => $data['address'] ?? null,
                'district' => $data['district'] ?? 'Mahendragarh',
                'state' => $data['state'] ?? 'Haryana',
                'pincode' => $data['pincode'] ?? '123001',
                'aadhaar_no' => $data['aadhaar_no'] ?? null,
                'scheme_id' => $scheme->id,
                'age_slab_id' => $slab ? $slab->id : null,
                'joining_amount' => $joiningAmount,
                'monthly_support_amount' => $monthlySupport,
                'agent_id' => $data['agent_id'] ?? null,
                'joining_date' => $joiningDate,
                'status' => 'Active',
                'pending_amount' => 0,
                'total_paid' => 0,
            ]);

            // 5. Handle Nominees (Multiple Nominee Support)
            if (!empty($data['nominee1_name'])) {
                Nominee::create([
                    'member_id' => $member->id,
                    'name' => $data['nominee1_name'],
                    'father_husband_name' => $data['nominee1_father'] ?? null,
                    'relation' => $data['nominee1_relation'] ?? 'Spouse',
                    'mobile' => $data['nominee1_mobile'] ?? null,
                    'aadhaar_no' => $data['nominee1_aadhaar'] ?? null,
                    'address' => $data['nominee1_address'] ?? $member->address,
                    'percentage' => $data['nominee1_share'] ?? 100.0,
                    'priority' => 1,
                ]);
            }

            if (!empty($data['nominee2_name'])) {
                Nominee::create([
                    'member_id' => $member->id,
                    'name' => $data['nominee2_name'],
                    'father_husband_name' => $data['nominee2_father'] ?? null,
                    'relation' => $data['nominee2_relation'] ?? 'Son',
                    'mobile' => $data['nominee2_mobile'] ?? null,
                    'aadhaar_no' => $data['nominee2_aadhaar'] ?? null,
                    'address' => $data['nominee2_address'] ?? $member->address,
                    'percentage' => $data['nominee2_share'] ?? 50.0,
                    'priority' => 2,
                ]);
            }

            // 6. Handle Document / Photo Uploads
            if (!empty($files)) {
                foreach ($files as $type => $file) {
                    if ($file && $file->isValid()) {
                        // Sanitise filename: random + safe original extension (prevents path traversal)
                        $safeExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
                        if (!preg_match('/^(jpg|jpeg|png|gif|pdf|webp)$/', $safeExt)) {
                            throw new \InvalidArgumentException("Unsupported file type for {$type}: .{$safeExt}");
                        }
                        $filename = 'member_' . $member->id . '_' . $type . '_' . time() . '_' . random_int(1000, 9999) . '.' . $safeExt;
                        $path = $file->storeAs('uploads/documents', $filename, 'public');

                        MemberDocument::create([
                            'member_id' => $member->id,
                            'document_type' => ucfirst($type),
                            'title' => ucfirst($type) . ' Document',
                            'file_path' => '/storage/' . $path,
                            'file_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'file_size' => $file->getSize(),
                            'uploaded_by' => auth()->check() ? auth()->id() : null,
                        ]);

                        if ($type === 'photo') {
                            $member->photo = '/storage/' . $path;
                            $member->save();
                        }
                    }
                }
            }

            // 7. Post Joining Fee Due in Ledger
            LedgerService::postEntry(
                memberId: $member->id,
                entryType: 'Joining Fee',
                description: 'Initial Membership Enrollment Fee Due',
                debit: $joiningAmount,
                credit: 0.0,
                transactionDate: $joiningDate,
                agentId: $member->agent_id
            );

            // 8. Process Initial Payment (if provided or default paid)
            $paidAmount = isset($data['initial_paid_amount']) ? (float)$data['initial_paid_amount'] : $joiningAmount;
            if ($paidAmount > 0) {
                PaymentService::processPayment([
                    'member_id' => $member->id,
                    'agent_id' => $member->agent_id,
                    'amount' => $paidAmount,
                    'payment_type' => 'Joining Fee',
                    'payment_mode' => $data['payment_mode'] ?? 'Cash',
                    'reference_no' => $data['reference_no'] ?? null,
                    'payment_date' => $joiningDate,
                    'remarks' => 'Initial Membership Enrollment Registration Fee',
                ]);
            }

            // 9. Generate Certificate Record
            $certNo = NumberSeriesService::getNextNumber('CRT', ['prefix' => 'CRT-' . Carbon::now()->format('Y') . '-', 'initial_value' => 8001, 'padding' => 4]);
            Certificate::create([
                'certificate_no' => $certNo,
                'member_id' => $member->id,
                'scheme_id' => $scheme->id,
                'issue_date' => $joiningDate,
                'authorized_by' => 'President / General Secretary',
                'verification_code' => strtoupper(substr(md5($certNo . $member->id), 0, 10)),
            ]);

            // 10. Log Activity
            AuditService::log('create', 'members', (string)$member->id, null, [
                'membership_no' => $member->membership_no,
                'name' => $member->full_name,
                'scheme' => $scheme->name,
                'joining_amount' => $joiningAmount
            ]);

            return $member;
        });
    }
}
