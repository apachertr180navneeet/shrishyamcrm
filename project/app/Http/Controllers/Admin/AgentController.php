<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\User;
use App\Models\Role;
use App\Services\NumberSeriesService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $user->agent_id) {
            return redirect()->route('admin.agents.show', $user->agent_id);
        }

        // Auto-ensure any user with agent role has an Agent record
        $agentUsers = User::where(function($q) {
            $q->where('role', 'agent')
              ->orWhereHas('roles', fn($rq) => $rq->where('name', 'agent'));
        })->get();

        foreach ($agentUsers as $au) {
            if (!$au->agent_id) {
                $agent = Agent::where('user_id', $au->id)
                    ->orWhere('email', $au->email)
                    ->orWhere('mobile', $au->phone)
                    ->first();

                if (!$agent) {
                    $agentCode = NumberSeriesService::getNextNumber('AGT', ['prefix' => 'AGT-', 'initial_value' => 1, 'padding' => 3]);
                    $code = str_replace('-', '', $agentCode);
                    $agent = Agent::create([
                        'agent_code' => $agentCode,
                        'name' => $au->full_name,
                        'code' => $code,
                        'mobile' => $au->phone,
                        'email' => $au->email,
                        'district' => $au->city ?: 'Mahendragarh',
                        'address' => $au->address ?: '',
                        'commission_rate' => 5.0,
                        'status' => 'Active',
                        'user_id' => $au->id,
                    ]);
                } else {
                    $agent->update(['user_id' => $au->id]);
                }

                $au->update(['agent_id' => $agent->id]);
            }
        }

        $query = Agent::with(['members', 'payments', 'user']);

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

        $agents = $query->latest('id')->paginate(15)->withQueryString();
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
            'name' => 'nullable|string|max:150',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'district' => 'required|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'password' => 'required|string|min:6',
        ]);

        // Compute full name, first name, last name
        if ($request->filled('first_name')) {
            $firstName = $request->first_name;
            $lastName = $request->last_name ?? '';
            $fullName = trim($firstName . ' ' . $lastName);
        } else {
            $fullName = $request->name ?: 'Agent User';
            $nameParts = explode(' ', trim($fullName));
            $firstName = $nameParts[0] ?? 'Agent';
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : ($request->district ?? 'Representative');
        }

        // 1. Unique agent code generation
        $agentCode = NumberSeriesService::getNextNumber('AGT', ['prefix' => 'AGT-', 'initial_value' => 1, 'padding' => 3]);
        $code = str_replace('-', '', $agentCode);
        $agentRole = Role::where('name', 'agent')->first();
        $email = $request->email ?: ('agent.' . strtolower(str_replace('-', '', $agentCode)) . '@shrishyam.org');

        // 2. Direct storage in Users table first
        $agentUser = User::where('email', $email)->orWhere('phone', $request->mobile)->first();

        if (!$agentUser) {
            $agentUser = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName,
                'slug' => Str::slug($fullName . '-' . rand(100, 999)),
                'email' => $email,
                'phone' => $request->mobile,
                'password' => Hash::make($request->password),
                'role' => 'agent',
                'role_id' => $agentRole?->id,
                'address' => $request->address ?? '',
                'city' => $request->district ?? '',
                'state' => 'Haryana',
                'country' => 'India',
                'status' => 'active',
            ]);
            if ($agentRole) {
                $agentUser->roles()->sync([$agentRole->id]);
            }
        } else {
            $agentUser->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName,
                'role' => 'agent',
                'role_id' => $agentRole?->id ?? $agentUser->role_id,
                'password' => Hash::make($request->password),
                'address' => $request->address ?? $agentUser->address,
                'city' => $request->district ?? $agentUser->city,
            ]);
            if ($agentRole) {
                $agentUser->roles()->sync([$agentRole->id]);
            }
        }

        // 3. Store in Agents table passing the user_id
        $agent = Agent::create([
            'agent_code' => $agentCode,
            'name' => $agentUser->full_name,
            'code' => $code,
            'mobile' => $agentUser->phone,
            'email' => $agentUser->email,
            'district' => $request->district,
            'address' => $request->address,
            'commission_rate' => $request->commission_rate,
            'status' => 'Active',
            'user_id' => $agentUser->id,
        ]);

        // 4. Pass agent_id back into user table
        $agentUser->update(['agent_id' => $agent->id]);

        return back()->with('success', "Agent {$agent->name} ({$agent->agent_code}) successfully created as user with login ID: {$agentUser->email}!");
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
