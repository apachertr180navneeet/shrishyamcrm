<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Services\NumberSeriesService;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $user->agent_id) {
            return redirect()->route('admin.agents.show', $user->agent_id);
        }

        $query = Agent::with(['members', 'payments']);

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
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
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            abort(403, 'Unauthorized. Agents cannot register new agents.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'district' => 'required|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Thread-safe, unique agent code generation
        $agentCode = NumberSeriesService::getNextNumber('AGT', ['prefix' => 'AGT-', 'initial_value' => 1, 'padding' => 3]);
        // Normalise the short "code" field to match the agent_code format
        $code = str_replace('-', '', $agentCode);

        Agent::create([
            'agent_code' => $agentCode,
            'name' => $request->name,
            'code' => $code,
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
        $user = auth()->user();
        if ($user && $user->isAgent() && $user->agent_id && (int)$id !== (int)$user->agent_id) {
            abort(403, 'Unauthorized access to other agent profiles.');
        }

        $agent = Agent::with(['members.scheme', 'payments.member'])->findOrFail($id);
        return view('admin.agents.show', compact('agent'));
    }
}
