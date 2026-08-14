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

        if ($request->filled('district')) {
            $query->where('district', $request->district);
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
        $nextMemNum = 'MEM-' . date('Y') . '-' . (1001 + Member::count());

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
            $files = [
                'photo' => $request->file('photo'),
                'aadhaar' => $request->file('aadhaar_doc'),
                'address' => $request->file('address_doc'),
            ];

            $member = MemberRegistrationService::register($request->all(), array_filter($files));

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
        $member = Member::findOrFail($id);
        $member->delete();
        return redirect()->route('admin.members.index')->with('success', 'Member record archived successfully.');
    }

    public function certificatePdf($id)
    {
        $pdf = CertificateService::generatePdf($id);
        return $pdf->download("SSWS_Certificate_{$id}.pdf");
    }
}
