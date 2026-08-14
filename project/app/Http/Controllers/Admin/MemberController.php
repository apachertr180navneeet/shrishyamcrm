<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Nominee;
use App\Models\Payment;
use Carbon\Carbon;
use DB;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::with(['scheme', 'agent', 'ageSlab', 'nominees']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('membership_no', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('aadhaar_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('scheme_id')) {
            $query->where('scheme_id', $request->scheme_id);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $members = $query->latest('id')->paginate(15)->withQueryString();
        $schemes = Scheme::where('status', 'Active')->get();
        $agents = Agent::where('status', 'Active')->get();

        return view('admin.members.index', compact('members', 'schemes', 'agents'));
    }

    public function create()
    {
        $schemes = Scheme::with('ageSlabs')->where('status', 'Active')->get();
        $agents = Agent::where('status', 'Active')->get();
        $nextMemNum = 'MEM-2026-' . (1000 + Member::count() + 1);

        return view('admin.members.create', compact('schemes', 'agents', 'nextMemNum'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'mobile' => 'required|string|max:20',
            'dob' => 'required|date',
            'scheme_id' => 'required|exists:schemes,id',
            'agent_id' => 'required|exists:agents,id',
        ]);

        try {
            DB::beginTransaction();

            $dob = Carbon::parse($request->dob);
            $age = $dob->age;

            $scheme = Scheme::findOrFail($request->scheme_id);
            $slab = AgeSlab::where('scheme_id', $scheme->id)
                ->where('status', 'Active')
                ->where('min_age', '<=', $age)
                ->where('max_age', '>=', $age)
                ->first() ?? AgeSlab::where('scheme_id', $scheme->id)->first();

            $joiningAmount = $slab ? $slab->joining_amount : 1100;
            $monthlySupport = $slab ? $slab->support_amount : 200;

            $memCount = Member::count() + 1;
            $memNo = $request->membership_no ?: ('MEM-2026-' . (1000 + $memCount));

            $member = Member::create([
                'membership_no' => $memNo,
                'full_name' => $request->full_name,
                'father_spouse_name' => $request->father_spouse_name,
                'mother_name' => $request->mother_name,
                'gender' => $request->gender ?? 'Male',
                'dob' => $dob->toDateString(),
                'age' => $age,
                'mobile' => $request->mobile,
                'gotra' => $request->gotra,
                'caste' => $request->caste,
                'address' => $request->address,
                'district' => $request->district ?? 'Mahendragarh',
                'state' => 'Haryana',
                'pincode' => $request->pincode ?? '123001',
                'aadhaar_no' => $request->aadhaar_no,
                'scheme_id' => $scheme->id,
                'age_slab_id' => $slab ? $slab->id : null,
                'joining_amount' => $joiningAmount,
                'monthly_support_amount' => $monthlySupport,
                'agent_id' => $request->agent_id,
                'joining_date' => $request->joining_date ?? now()->toDateString(),
                'status' => 'Active',
                'pending_amount' => 0,
                'total_paid' => $joiningAmount,
            ]);

            // Save Nominee 1
            if ($request->filled('nominee1_name')) {
                Nominee::create([
                    'member_id' => $member->id,
                    'name' => $request->nominee1_name,
                    'relation' => $request->nominee1_relation ?? 'Spouse',
                    'mobile' => $request->nominee1_mobile,
                    'aadhaar_no' => $request->nominee1_aadhaar,
                    'priority' => 1,
                ]);
            }

            // Save Nominee 2
            if ($request->filled('nominee2_name')) {
                Nominee::create([
                    'member_id' => $member->id,
                    'name' => $request->nominee2_name,
                    'relation' => $request->nominee2_relation ?? 'Son',
                    'mobile' => $request->nominee2_mobile,
                    'aadhaar_no' => $request->nominee2_aadhaar,
                    'priority' => 2,
                ]);
            }

            // Record Initial Joining Payment
            $receiptNo = 'REC-2026-' . (5000 + Payment::count() + 1);
            $agent = Agent::find($request->agent_id);

            Payment::create([
                'receipt_no' => $receiptNo,
                'san_code' => 'SAN-LOH-' . str_pad($member->id, 3, '0', STR_PAD_LEFT),
                'member_id' => $member->id,
                'agent_id' => $agent ? $agent->id : null,
                'amount' => $joiningAmount,
                'payment_type' => 'Joining Fee',
                'payment_mode' => $request->payment_mode ?? 'UPI',
                'reference_no' => $request->reference_no ?? ('TXN' . rand(100000, 999999)),
                'month_year' => Carbon::now()->format('M Y'),
                'payment_date' => now()->toDateString(),
                'status' => 'Verified',
                'collected_by' => $agent ? $agent->name : 'HQ Admin',
                'remarks' => 'Initial Membership Registration Fee',
            ]);

            DB::commit();

            return redirect()->route('admin.members.show', $member->id)->with('success', "Member {$member->full_name} enrolled successfully with Membership No: {$member->membership_no}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating member: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $member = Member::with(['scheme', 'ageSlab', 'agent', 'nominees', 'payments'])->findOrFail($id);
        return view('admin.members.show', compact('member'));
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();
        return redirect()->route('admin.members.index')->with('success', 'Member deleted successfully.');
    }
}
