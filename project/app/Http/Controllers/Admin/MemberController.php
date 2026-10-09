<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
use App\Models\Nominee;
use App\Models\MemberDocument;
use App\Services\MemberRegistrationService;
use App\Services\CertificateService;
use App\Services\WhatsAppService;
use App\Services\NumberSeriesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'agent', 'ageSlab', 'nominees']);

        // Agent-level backend query scoping
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        if ($request->filled('search')) {
            // Escape LIKE wildcards so user input matches literally (MySQL default ESCAPE '\')
            $search = \App\Helpers\Helper::likeEscape($request->search);
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

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $members = $query->latest('id')->paginate(15)->withQueryString();
        $schemes = Scheme::where('status', 'Active')->get();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->get();

        return view('admin.members.index', compact('members', 'schemes', 'agents'));
    }

    public function create()
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $schemes = Scheme::with('ageSlabs')->where('status', 'Active')->get();
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->get();
        // Use the thread-safe number series to anticipate the next membership number
        $nextMemNum = NumberSeriesService::peekNextNumber('MEM', ['prefix' => 'MEM-' . date('Y') . '-', 'initial_value' => 1001, 'padding' => 4]);

        return view('admin.members.create', compact('schemes', 'agents', 'nextMemNum'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $user->agent_id) {
            $request->merge(['agent_id' => $user->agent_id]);
        }

        $request->validate([
            'full_name' => 'required|string|max:150',
            'mobile' => 'required|string|max:20',
            'dob' => 'required|date',
            'scheme_id' => 'required|exists:schemes,id',
            'agent_id' => 'required|exists:agents,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $files = [
                'photo' => $request->file('photo'),
                'aadhaar' => $request->file('aadhaar_doc'),
                'address' => $request->file('address_doc'),
            ];

            // Pass only expected member fields (prevent mass assignment injection)
            $memberData = $request->only([
                'membership_no', 'full_name', 'father_spouse_name', 'mother_name', 'gender',
                'dob', 'mobile', 'gotra', 'caste', 'address', 'district', 'state', 'pincode',
                'aadhaar_no', 'scheme_id', 'age_slab_id', 'joining_amount', 'monthly_support_amount',
                'agent_id', 'joining_date', 'payment_mode',
                'reference_no', 'initial_paid_amount', 'nominee1_name', 'nominee1_father',
                'nominee1_relation', 'nominee1_mobile', 'nominee1_aadhaar', 'nominee1_address',
                'nominee1_share', 'nominee2_name', 'nominee2_father', 'nominee2_relation',
                'nominee2_mobile', 'nominee2_aadhaar', 'nominee2_address', 'nominee2_share',
            ]);

            $member = MemberRegistrationService::register($memberData, array_filter($files));

            return redirect()->route('admin.members.show', $member->id)
                ->with('success', "Member {$member->full_name} enrolled successfully with Membership No: {$member->membership_no}!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error enrolling member: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'ageSlab', 'agent', 'nominees', 'payments', 'documents', 'ledgers', 'certificates']);

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        $member = $query->findOrFail($id);
        $whatsappData = WhatsAppService::getDueReminderMessage($member);

        return view('admin.members.show', compact('member', 'whatsappData'));
    }

    public function edit(Request $request, $id)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'ageSlab', 'agent', 'nominees', 'documents']);
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($member);
        }

        $schemes = Scheme::with(['ageSlabs' => fn($q) => $q->where('status', 'Active')->orderBy('min_age')])->where('status', 'Active')->get();
        $agents = Agent::where('status', 'Active')->orderBy('name')->get();
        $ageSlabs = AgeSlab::where('status', 'Active')->orderBy('min_age')->get();

        return view('admin.members.edit', compact('member', 'schemes', 'agents', 'ageSlabs'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:150',
            'mobile' => 'required|string|max:20',
            'father_spouse_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'gender' => 'required|string|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:120',
            'gotra' => 'nullable|string|max:100',
            'caste' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhaar_no' => 'nullable|string|max:20',
            'status' => 'required|string|in:Active,Inactive,Suspended',
            'scheme_id' => 'required|exists:schemes,id',
            'age_slab_id' => 'nullable|exists:age_slabs,id',
            'agent_id' => 'nullable|exists:agents,id',
            'monthly_support_amount' => 'nullable|numeric|min:0',
            'joining_amount' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'document_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        $dob = $request->filled('dob') ? Carbon::parse($request->dob) : $member->dob;
        $age = $dob ? $dob->age : ($request->input('age', $member->age));

        $memberData = [
            'full_name' => $request->full_name,
            'father_spouse_name' => $request->father_spouse_name,
            'mother_name' => $request->mother_name,
            'gender' => $request->gender,
            'dob' => $dob ? $dob->toDateString() : null,
            'age' => $age,
            'mobile' => $request->mobile,
            'gotra' => $request->gotra,
            'caste' => $request->caste,
            'address' => $request->address,
            'district' => $request->district ?? $member->district,
            'state' => $request->state ?? $member->state,
            'pincode' => $request->pincode,
            'aadhaar_no' => $request->aadhaar_no,
            'status' => $request->status,
            'scheme_id' => $request->scheme_id ?? $member->scheme_id,
            'age_slab_id' => $request->age_slab_id ?? $member->age_slab_id,
            'agent_id' => $request->agent_id ?? $member->agent_id,
        ];

        if ($request->filled('monthly_support_amount')) {
            $memberData['monthly_support_amount'] = (float)$request->monthly_support_amount;
        }
        if ($request->filled('joining_amount')) {
            $memberData['joining_amount'] = (float)$request->joining_amount;
        }
        if ($request->filled('joining_date')) {
            $memberData['joining_date'] = $request->joining_date;
        }

        // Handle Photo Upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $safeExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $filename = 'member_' . $member->id . '_photo_' . time() . '_' . random_int(1000, 9999) . '.' . $safeExt;
            $path = $file->storeAs('uploads/documents', $filename, 'public');
            // Store full image URL in database
            $memberData['photo'] = asset('storage/' . $path);

            MemberDocument::create([
                'member_id' => $member->id,
                'document_type' => 'Photo',
                'title' => 'Member Profile Photo',
                'file_path' => '/storage/' . $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->check() ? auth()->id() : null,
            ]);
        }

        // Handle Additional Document Upload
        if ($request->hasFile('document_file') && $request->file('document_file')->isValid()) {
            $file = $request->file('document_file');
            $safeExt = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $docType = $request->input('document_type', 'Identity');
            $filename = 'member_' . $member->id . '_' . strtolower($docType) . '_' . time() . '_' . random_int(1000, 9999) . '.' . $safeExt;
            $path = $file->storeAs('uploads/documents', $filename, 'public');

            MemberDocument::create([
                'member_id' => $member->id,
                'document_type' => ucfirst($docType),
                'title' => ucfirst($docType) . ' Document',
                'file_path' => '/storage/' . $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->check() ? auth()->id() : null,
            ]);
        }

        $member->update($memberData);

        // Nominee 1
        if ($request->filled('nominee1_name')) {
            $n1 = Nominee::where('member_id', $member->id)->where('priority', 1)->first();
            $n1Data = [
                'name' => $request->nominee1_name,
                'father_husband_name' => $request->nominee1_father ?? null,
                'relation' => $request->nominee1_relation ?? 'Spouse',
                'mobile' => $request->nominee1_mobile ?? null,
                'aadhaar_no' => $request->nominee1_aadhaar ?? null,
                'address' => $request->nominee1_address ?? $member->address,
                'percentage' => $request->nominee1_share ?? 100.0,
                'priority' => 1,
            ];
            if ($n1) {
                $n1->update($n1Data);
            } else {
                $n1Data['member_id'] = $member->id;
                Nominee::create($n1Data);
            }
        }

        // Nominee 2
        if ($request->filled('nominee2_name')) {
            $n2 = Nominee::where('member_id', $member->id)->where('priority', 2)->first();
            $n2Data = [
                'name' => $request->nominee2_name,
                'father_husband_name' => $request->nominee2_father ?? null,
                'relation' => $request->nominee2_relation ?? 'Son',
                'mobile' => $request->nominee2_mobile ?? null,
                'aadhaar_no' => $request->nominee2_aadhaar ?? null,
                'address' => $request->nominee2_address ?? $member->address,
                'percentage' => $request->nominee2_share ?? 50.0,
                'priority' => 2,
            ];
            if ($n2) {
                $n2->update($n2Data);
            } else {
                $n2Data['member_id'] = $member->id;
                Nominee::create($n2Data);
            }
        }

        return redirect()->route('admin.members.show', $member->id)->with('success', "Member {$member->full_name} ({$member->membership_no}) details updated successfully.");
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        $member->delete();
        return redirect()->route('admin.members.index')->with('success', 'Member record archived successfully.');
    }

    public function certificatePdf($id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $query->findOrFail($id); // authorization check (404 if not scoped)
        $pdf = CertificateService::generatePdf($id);
        return $pdf->download("SSWS_Certificate_{$id}.pdf");
    }

    public function ledgerPdf($id, Request $request)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        $pdf = \App\Services\MemberLedgerCardService::generatePdf($member->id);
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $member->membership_no . '_' . $member->full_name);
        $fileName = "Ledger_Statement_{$cleanName}.pdf";

        if ($request->get('action') === 'stream' || $request->get('view') === '1') {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }
}
