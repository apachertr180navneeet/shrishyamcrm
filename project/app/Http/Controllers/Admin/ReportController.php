<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use App\Models\MarriageEvent;
use App\Models\AgentCommission;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const ALLOWED_TYPES = ['collection', 'agent', 'pending', 'commission', 'members', 'events', 'monthly', 'payments'];

    private function normaliseType(?string $type): string
    {
        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'collection';
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $type = $this->normaliseType($request->get('type'));
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->get();

        $data = [];

        switch ($type) {
            case 'agent':
                $query = Agent::with(['members', 'payments']);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('id', $user->agent_id);
                }
                $data = $query->get();
                break;

            case 'pending':
                $query = Member::with(['scheme', 'agent'])->where('pending_amount', '>', 0);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $data = $query->get();
                break;

            case 'commission':
                $query = AgentCommission::with(['agent', 'payment', 'member']);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $data = $query->latest()->get();
                break;

            case 'members':
                $query = Member::with(['scheme', 'agent', 'ageSlab']);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $data = $query->get();
                break;

            case 'events':
                $data = MarriageEvent::with(['member', 'payouts', 'billings'])->get();
                break;

            case 'monthly':
                $query = Payment::selectRaw('month_year, count(*) as count, sum(amount) as total')
                    ->where('status', 'Verified');
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $data = $query->groupBy('month_year')->get();
                break;

            case 'collection':
            case 'payments':
            default:
                $query = Payment::with(['member.scheme', 'agent'])->where('status', 'Verified');
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $data = $query->latest('payment_date')->get();
                break;
        }

        return view('admin.reports.index', compact('type', 'data', 'agents'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $type = $this->normaliseType($request->get('type'));
        $fileName = "SSWS_Report_{$type}_" . date('Ymd_His') . ".csv";

        $response = new StreamedResponse(function () use ($type, $user) {
            $handle = fopen('php://output', 'w');

            if ($type === 'members') {
                fputcsv($handle, ['Membership No', 'Full Name', 'Mobile', 'Scheme', 'Age', 'District', 'Agent', 'Joining Amount', 'Monthly Support', 'Status', 'Pending Dues']);
                $query = Member::with(['scheme', 'agent']);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $query->chunk(500, function ($members) use ($handle) {
                    foreach ($members as $m) {
                        fputcsv($handle, [
                            $m->membership_no,
                            $m->full_name,
                            $m->mobile,
                            $m->scheme ? $m->scheme->name_hindi : '',
                            $m->age,
                            $m->district,
                            $m->agent ? $m->agent->name : '',
                            $m->joining_amount,
                            $m->monthly_support_amount,
                            $m->status,
                            $m->pending_amount,
                        ]);
                    }
                });
            } elseif ($type === 'pending') {
                fputcsv($handle, ['Membership No', 'Full Name', 'Mobile', 'Scheme', 'Agent', 'Pending Amount (₹)', 'Status']);
                $query = Member::with(['scheme', 'agent'])->where('pending_amount', '>', 0);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $query->chunk(500, function ($members) use ($handle) {
                    foreach ($members as $m) {
                        fputcsv($handle, [
                            $m->membership_no,
                            $m->full_name,
                            $m->mobile,
                            $m->scheme ? $m->scheme->name_hindi : '',
                            $m->agent ? $m->agent->name : '',
                            $m->pending_amount,
                            $m->status,
                        ]);
                    }
                });
            } elseif ($type === 'agent') {
                fputcsv($handle, ['Agent Code', 'Name', 'Mobile', 'District', 'Total Members', 'Total Collection (₹)', 'Commission Rate (%)', 'Commission Due (₹)']);
                $query = Agent::with(['members', 'payments']);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('id', $user->agent_id);
                }
                $query->chunk(500, function ($agents) use ($handle) {
                    foreach ($agents as $a) {
                        fputcsv($handle, [
                            $a->agent_code,
                            $a->name,
                            $a->mobile,
                            $a->district,
                            $a->members->count(),
                            $a->total_collection,
                            $a->commission_rate . '%',
                            $a->total_commission,
                        ]);
                    }
                });
            } elseif ($type === 'commission') {
                fputcsv($handle, ['Agent Code', 'Agent Name', 'Member Name', 'Receipt No', 'Collection (₹)', 'Rate (%)', 'Commission Amount (₹)', 'Status', 'Date']);
                $query = AgentCommission::with(['agent', 'payment', 'member']);
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $query->chunk(500, function ($commissions) use ($handle) {
                    foreach ($commissions as $c) {
                        fputcsv($handle, [
                            $c->agent ? $c->agent->agent_code : '',
                            $c->agent ? $c->agent->name : '',
                            $c->member ? $c->member->full_name : '',
                            $c->payment ? $c->payment->receipt_no : '',
                            $c->collection_amount,
                            $c->commission_rate . '%',
                            $c->commission_amount,
                            $c->status,
                            $c->created_at->format('Y-m-d'),
                        ]);
                    }
                });
            } else {
                fputcsv($handle, ['Receipt No', 'SAN Code', 'Member Name', 'Membership No', 'Amount (₹)', 'Payment Type', 'Payment Mode', 'Date', 'Status']);
                $query = Payment::with('member')->where('status', 'Verified');
                if ($user && $user->isAgent() && $user->agent_id) {
                    $query->where('agent_id', $user->agent_id);
                }
                $query->orderBy('payment_date')->chunk(500, function ($payments) use ($handle) {
                    foreach ($payments as $p) {
                        fputcsv($handle, [
                            $p->receipt_no,
                            $p->san_code,
                            $p->member ? $p->member->full_name : '',
                            $p->member ? $p->member->membership_no : '',
                            $p->amount,
                            $p->payment_type,
                            $p->payment_mode,
                            $p->payment_date ? $p->payment_date->format('Y-m-d') : '',
                            $p->status,
                        ]);
                    }
                });
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }
}
