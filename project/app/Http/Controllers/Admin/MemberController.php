<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\AgeSlab;
use App\Models\Agent;
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
                'aadhaar_no', 'scheme_id', 'agent_id', 'joining_date', 'payment_mode',
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
}
