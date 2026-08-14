<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::with(['members', 'payments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('agent_code', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%");
            });
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $agents = $query->paginate(15)->withQueryString();
        $districts = Agent::distinct()->pluck('district')->filter();

        return view('admin.agents.index', compact('agents', 'districts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'mobile' => 'required|string|max:20',
            'district' => 'required|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $count = Agent::count() + 1;
        $agentCode = 'AGT-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        Agent::create([
            'agent_code' => $agentCode,
            'name' => $request->name,
            'code' => 'AGT' . str_pad($count, 2, '0', STR_PAD_LEFT),
            'mobile' => $request->mobile,
            'email' => $request->email,
            'district' => $request->district,
            'address' => $request->address,
            'commission_rate' => $request->commission_rate,
            'status' => 'Active',
        ]);

        return back()->with('success', 'Agent registered successfully!');
    }

    public function show($id)
    {
        $agent = Agent::with(['members.scheme', 'payments.member'])->findOrFail($id);
        return view('admin.agents.show', compact('agent'));
    }
}
