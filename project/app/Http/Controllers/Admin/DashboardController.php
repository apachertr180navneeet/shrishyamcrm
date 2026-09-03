<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Agent;
use App\Models\Payment;
use App\Models\MarriageEvent;
use App\Models\Payout;
use App\Models\Scheme;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $agentId = $isAgent ? $user->agent_id : null;

        // Members metrics
        $memberQuery = Member::query();
        if ($isAgent) {
            $memberQuery->where('agent_id', $agentId);
        }

        $totalMembers = (clone $memberQuery)->count();
        $activeMembers = (clone $memberQuery)->where('status', 'Active')->count();
        $inactiveMembers = (clone $memberQuery)->where('status', '!=', 'Active')->count();
        $totalAgents = $isAgent ? 1 : Agent::where('status', 'Active')->count();

        // Payment metrics
        $paymentQuery = Payment::where('status', 'Verified');
        if ($isAgent) {
            $paymentQuery->where('agent_id', $agentId);
        }

        $today = Carbon::today();
        $todayCollection = (clone $paymentQuery)->whereDate('payment_date', $today)->sum('amount');

        $startOfMonth = Carbon::now()->startOfMonth();
        $monthCollection = (clone $paymentQuery)->where('payment_date', '>=', $startOfMonth)->sum('amount');

        $pendingAmountSum = (clone $memberQuery)->sum('pending_amount');
        $pendingPaymentsCount = (clone $memberQuery)->where('pending_amount', '>', 0)->count();
        $totalEvents = MarriageEvent::count();
        $totalPayouts = Payout::where('status', 'Disbursed')->sum('amount');

        // Scheme distribution
        $seniorScheme = Scheme::where('code', 'SENIOR')->first();
        $marriageScheme = Scheme::where('code', 'MARRIAGE')->first();
        $seniorMembersCount = $seniorScheme ? (clone $memberQuery)->where('scheme_id', $seniorScheme->id)->count() : 0;
        $marriageMembersCount = $marriageScheme ? (clone $memberQuery)->where('scheme_id', $marriageScheme->id)->count() : 0;

        // Top Agents
        $topAgents = $isAgent
            ? Agent::where('id', $agentId)->withSum('payments', 'amount')->get()
            : Agent::withSum('payments', 'amount')->orderBy('payments_sum_amount', 'desc')->take(5)->get();

        // Recent Payments
        $recentPayments = (clone $paymentQuery)->with(['member', 'agent'])->latest('payment_date')->take(6)->get();

        // Monthly Trend Data for Chart.js (Aggregated in 2 group-by queries instead of 24 separate queries)
        $twelveMonthsAgo = Carbon::now()->subMonths(11)->startOfMonth();

        $paymentsByMonth = (clone $paymentQuery)
            ->where('payment_date', '>=', $twelveMonthsAgo)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount) as total_amount")
            ->groupBy('ym')
            ->pluck('total_amount', 'ym');

        $membersByMonth = (clone $memberQuery)
            ->where('joining_date', '>=', $twelveMonthsAgo)
            ->selectRaw("DATE_FORMAT(joining_date, '%Y-%m') as ym, COUNT(*) as total_count")
            ->groupBy('ym')
            ->pluck('total_count', 'ym');

        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $ym = $date->format('Y-m');
            $mLabel = $date->format('M Y');

            $monthlyTrend[] = [
                'month' => $mLabel,
                'collection' => (float)($paymentsByMonth[$ym] ?? 0),
                'new_members' => (int)($membersByMonth[$ym] ?? 0),
            ];
        }

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
            'totalPayouts',
            'seniorMembersCount',
            'marriageMembersCount',
            'topAgents',
            'recentPayments',
            'monthlyTrend',
            'isAgent'
        ));
    }
}
