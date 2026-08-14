<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::with(['scheme', 'agent']);

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

        return view('admin.certificates.index', compact('members'));
    }

    public function show($id)
    {
        $member = Member::with(['scheme', 'agent', 'nominees'])->findOrFail($id);
        return view('admin.certificates.show', compact('member'));
    }
}
