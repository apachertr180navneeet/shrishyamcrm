<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SocietySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptService
{
    /**
     * Generate printable / downloadable PDF receipt
     */
    public static function generatePdf(int $paymentId): \Barryvdh\DomPDF\PDF
    {
        $payment = Payment::with(['member.scheme', 'member.agent', 'member.nominees', 'agent', 'event'])->findOrFail($paymentId);

        $society = [
            'name' => SocietySetting::getVal('society_name', 'Shri Shyam Welfare Society'),
            'name_hindi' => SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसाइटी, लोहिड़ी'),
            'reg_no' => SocietySetting::getVal('reg_no', 'COOP/2025/BALOTARA/500219'),
            'san_prefix' => SocietySetting::getVal('san_prefix', '0878920000000014'),
            'address' => SocietySetting::getVal('address', 'ग्राम पंचायत लोहिड़ी, पंचायत समिति सिणधरी, जिला बालोतरा (राजस्थान)'),
            'phone' => SocietySetting::getVal('phone', '9664090906, 9549635631, 9783049650'),
            'email' => SocietySetting::getVal('email', 'info@shrishyamwelfare.org'),
            'president' => SocietySetting::getVal('president_name', 'Shri Navneet Sharma'),
            'secretary' => SocietySetting::getVal('secretary_name', 'Shri Mahesh Garg'),
        ];

        MemberLedgerCardService::ensureFontsExist();
        $mangalPath = str_replace('\\', '/', public_path('fonts/mangal.ttf'));
        $mangalbPath = str_replace('\\', '/', public_path('fonts/mangalb.ttf'));
        $aparajPath = str_replace('\\', '/', public_path('fonts/aparaj.ttf'));
        $aparajbPath = str_replace('\\', '/', public_path('fonts/aparajb.ttf'));
        $logoPath = str_replace('\\', '/', public_path('assets/society_logo.jpg'));
        if (!file_exists($logoPath)) {
            $logoPath = str_replace('\\', '/', public_path('assets/society_logo.png'));
        }

        $member = $payment->member;
        $primaryNominee = $member && $member->nominees ? $member->nominees->first() : null;
        $nomineeName = $primaryNominee ? $primaryNominee->name . ($primaryNominee->relation ? ' (' . $primaryNominee->relation . ')' : '') : '-';

        $kishtRate = $member && (float)$member->monthly_support_amount > 0
            ? (float)$member->monthly_support_amount
            : (float)$payment->amount;

        $sanCode = $payment->san_code ?: ($member && $member->san_code ? $member->san_code : $society['san_prefix']);

        $agentName = $payment->agent
            ? $payment->agent->name
            : ($member && $member->agent ? $member->agent->name : ($payment->collected_by ?: 'HQ Administration'));

        $agentMobile = $payment->agent
            ? $payment->agent->mobile
            : ($member && $member->agent ? $member->agent->mobile : ($society['phone'] ? explode(',', $society['phone'])[0] : ''));

        $joiningDate = $member && $member->joining_date
            ? $member->joining_date->format('d.m.Y')
            : ($member && $member->created_at ? $member->created_at->format('d.m.Y') : '-');

        $eventDate = $payment->event && $payment->event->event_date
            ? $payment->event->event_date->format('d.m.Y')
            : ($payment->payment_date ? $payment->payment_date->format('d.m.Y') : '-');

        $paymentDate = $payment->payment_date ? $payment->payment_date->format('d.m.Y') : date('d.m.Y');

        $description = '';
        if ($payment->event) {
            $ev = $payment->event;
            $description = $ev->girl_name . ($ev->father_name ? ' / ' . $ev->father_name : '') . ($ev->venue ? ' ' . $ev->venue : '');
        } else {
            $description = $payment->payment_type . ($payment->remarks ? ' - ' . $payment->remarks : ($payment->month_year ? ' (' . $payment->month_year . ')' : ''));
        }

        return Pdf::loadView('pdf.receipt', compact(
            'payment',
            'member',
            'society',
            'nomineeName',
            'kishtRate',
            'sanCode',
            'agentName',
            'agentMobile',
            'joiningDate',
            'eventDate',
            'paymentDate',
            'description',
            'logoPath',
            'mangalPath',
            'mangalbPath',
            'aparajPath',
            'aparajbPath'
        ))->setPaper('a5', 'landscape');
    }
}
