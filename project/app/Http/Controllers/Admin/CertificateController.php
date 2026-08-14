<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Scheme;
use App\Services\CertificateService;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Member::with(['scheme', 'agent', 'certificates']);

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
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
        $query = Member::with(['scheme', 'agent', 'nominees', 'certificates']);
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        $member = $query->findOrFail($id);
        return view('admin.certificates.show', compact('member'));
    }

    public function downloadPdf($id)
    {
        $pdf = CertificateService::generatePdf($id);
        return $pdf->download("SSWS_Certificate_{$id}.pdf");
    }
}
