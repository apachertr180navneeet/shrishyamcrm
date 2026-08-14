<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use App\Models\MarriageEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'collection');
        $agents = Agent::where('status', 'Active')->get();

        $data = [];

        switch ($type) {
            case 'agent':
                $data = Agent::with(['members', 'payments'])->get();
                break;
            case 'pending':
                $data = Member::with(['scheme', 'agent'])->where('pending_amount', '>', 0)->get();
                break;
            case 'commission':
                $data = Agent::with('payments')->get();
                break;
            case 'members':
                $data = Member::with(['scheme', 'agent', 'ageSlab'])->get();
                break;
            case 'events':
                $data = MarriageEvent::with('member')->get();
                break;
            case 'monthly':
                $data = Payment::selectRaw('month_year, count(*) as count, sum(amount) as total')
                    ->where('status', 'Verified')
                    ->groupBy('month_year')
                    ->get();
                break;
            case 'collection':
            case 'payments':
            default:
                $data = Payment::with(['member.scheme', 'agent'])->where('status', 'Verified')->latest('payment_date')->get();
                break;
        }

        return view('admin.reports.index', compact('type', 'data', 'agents'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'collection');
        $fileName = "SSWS_Report_{$type}_" . date('Ymd_His') . ".csv";

        $response = new StreamedResponse(function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'members') {
                fputcsv($handle, ['Membership No', 'Full Name', 'Mobile', 'Scheme', 'Age', 'District', 'Agent', 'Joining Amount', 'Monthly Support', 'Status']);
                $members = Member::with(['scheme', 'agent'])->get();
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
                    ]);
                }
            } elseif ($type === 'pending') {
                fputcsv($handle, ['Membership No', 'Full Name', 'Mobile', 'Scheme', 'Agent', 'Pending Amount (₹)', 'Status']);
                $members = Member::with(['scheme', 'agent'])->where('pending_amount', '>', 0)->get();
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
            } elseif ($type === 'agent' || $type === 'commission') {
                fputcsv($handle, ['Agent Code', 'Name', 'Mobile', 'District', 'Total Members', 'Total Collection (₹)', 'Commission Rate (%)', 'Commission Due (₹)']);
                $agents = Agent::with(['members', 'payments'])->get();
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
            } else {
                fputcsv($handle, ['Receipt No', 'SAN Code', 'Member Name', 'Membership No', 'Amount (₹)', 'Payment Type', 'Payment Mode', 'Date', 'Status']);
                $payments = Payment::with('member')->get();
                foreach ($payments as $p) {
                    fputcsv($handle, [
                        $p->receipt_no,
                        $p->san_code,
                        $p->member ? $p->member->full_name : '',
                        $p->member ? $p->member->membership_no : '',
                        $p->amount,
                        $p->payment_type,
                        $p->payment_mode,
                        $p->payment_date->format('Y-m-d'),
                        $p->status,
                    ]);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }
}
