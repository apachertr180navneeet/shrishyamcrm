<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Scheme;
use App\Services\CertificateService;

class CertificateController extends Controller
{
    public function __construct()
    {
        // Enforce Admin only access
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user && $user->isAgent() && !$user->hasRole(['admin', 'super_admin'])) {
                abort(403, 'Unauthorized. Certificates can only be accessed by Admin.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'agent', 'certificates']);

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('membership_no', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('scheme_id')) {
            $query->where('scheme_id', $request->scheme_id);
        }

        $members = $query->paginate(15)->withQueryString();
        $schemes = Scheme::where('status', 'Active')->get();

        return view('admin.certificates.index', compact('members', 'schemes'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'agent', 'nominees', 'certificates', 'ageSlab', 'documents']);
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        $member = $query->findOrFail($id);

        $society = [
            'name' => \App\Models\SocietySetting::getVal('society_name', 'Shri Shyam Welfare Society'),
            'name_hindi' => \App\Models\SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहिड़ी'),
            'reg_no' => \App\Models\SocietySetting::getVal('reg_no', 'COOP/2025/BALOTARA/500219'),
            'san_code' => $member->san_code ?: \App\Models\SocietySetting::getVal('san_prefix', '0878920000000014'),
            'address' => \App\Models\SocietySetting::getVal('address', 'ग्रा.पं. लोहिड़ी, पं.स. सिणधरी, जिला बालोतरा (राज.)'),
            'phone' => \App\Models\SocietySetting::getVal('phone', '9664090906, 9549635631, 9783049650'),
            'president' => \App\Models\SocietySetting::getVal('president_name', 'लादूराम'),
            'founder' => \App\Models\SocietySetting::getVal('founder_name', 'लादूराम'),
        ];

        $primaryNominee = $member->nominees->first();
        $nomineeName = $primaryNominee
            ? $primaryNominee->name . ($primaryNominee->relation ? ' (' . $primaryNominee->relation . ')' : '')
            : '-';

        $kishtRate = (float)($member->monthly_support_amount > 0
            ? $member->monthly_support_amount
            : ($member->ageSlab && (float)$member->ageSlab->support_amount > 0 ? $member->ageSlab->support_amount : 400.0));

        $photoDoc = $member->documents->where('document_type', 'Photo')->first();

        return view('admin.certificates.show', compact('member', 'society', 'nomineeName', 'kishtRate', 'photoDoc'));
    }

    public function downloadPdf($id)
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
