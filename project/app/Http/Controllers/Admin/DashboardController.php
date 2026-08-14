<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Agent;
use App\Models\Payment;
use App\Models\MarriageEvent;
use App\Models\Scheme;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'Active')->count();
        $inactiveMembers = Member::where('status', '!=', 'Active')->count();
        $totalAgents = Agent::where('status', 'Active')->count();

        $today = Carbon::today();
        $todayCollection = Payment::whereDate('payment_date', $today)->where('status', 'Verified')->sum('amount');

        $startOfMonth = Carbon::now()->startOfMonth();
        $monthCollection = Payment::where('payment_date', '>=', $startOfMonth)->where('status', 'Verified')->sum('amount');

        $pendingAmountSum = Member::sum('pending_amount');
        $pendingPaymentsCount = Member::where('pending_amount', '>', 0)->count();
        $totalEvents = MarriageEvent::count();

        // Scheme distribution
        $seniorScheme = Scheme::where('code', 'SENIOR')->first();
        $marriageScheme = Scheme::where('code', 'MARRIAGE')->first();
        $seniorMembersCount = $seniorScheme ? Member::where('scheme_id', $seniorScheme->id)->count() : 0;
        $marriageMembersCount = $marriageScheme ? Member::where('scheme_id', $marriageScheme->id)->count() : 0;

        // Top Agents
        $topAgents = Agent::withSum('payments', 'amount')->orderBy('payments_sum_amount', 'desc')->take(5)->get();

        // Recent Payments
        $recentPayments = Payment::with(['member', 'agent'])->latest('payment_date')->take(6)->get();

        return view('admin.dashboard.index', compact(
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'totalAgents',
            'todayCollection',
            'monthCollection',
            'pendingAmountSum',
            'pendingPaymentsCount',
            'totalEvents',
            'seniorMembersCount',
            'marriageMembersCount',
            'topAgents',
            'recentPayments'
        ));
    }
}
