<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsAppLog;
use App\Models\Member;
use App\Models\Payment;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
    public function index()
    {
        $logs = WhatsAppLog::with('member')->latest('id')->paginate(15);
        $members = Member::where('status', 'Active')->get();
        $recentPayments = Payment::with('member')->latest('id')->take(10)->get();

        return view('admin.whatsapp.index', compact('logs', 'members', 'recentPayments'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:150',
            'mobile' => 'required|string|max:20',
            'message_type' => 'required|string',
            'message_body' => 'required|string',
        ]);

        $cleanMobile = preg_replace('/[^0-9]/', '', $request->mobile);
        if (strlen($cleanMobile) === 10) {
            $cleanMobile = '91' . $cleanMobile;
        }

        WhatsAppLog::create([
            'member_id' => $request->member_id,
            'recipient_name' => $request->recipient_name,
            'mobile' => $request->mobile,
            'message_type' => $request->message_type,
            'message_body' => $request->message_body,
            'status' => 'Sent',
            'sent_at' => Carbon::now(),
        ]);

        $encodedText = urlencode($request->message_body);
        $waUrl = "https://api.whatsapp.com/send?phone={$cleanMobile}&text={$encodedText}";

        return redirect()->away($waUrl);
    }
}
