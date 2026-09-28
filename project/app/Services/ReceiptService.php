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
        $payment = Payment::with(['member.scheme', 'member.agent', 'agent'])->findOrFail($paymentId);

        $society = [
            'name' => SocietySetting::getVal('society_name', 'Shri Shyam Welfare Society'),
            'name_hindi' => SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहीकी'),
            'reg_no' => SocietySetting::getVal('reg_no', 'HR/019/2021/04582'),
            'san_prefix' => SocietySetting::getVal('san_prefix', 'SAN-LOH'),
            'address' => SocietySetting::getVal('address', 'Main Bazar, Lohki, District Mahendragarh, Haryana - 123001'),
            'phone' => SocietySetting::getVal('phone', '+91 98290 12345'),
            'email' => SocietySetting::getVal('email', 'info@shrishyamwelfare.org'),
            'president' => SocietySetting::getVal('president_name', 'Shri R. K. Sharma'),
            'secretary' => SocietySetting::getVal('secretary_name', 'Shri Mahesh Garg'),
        ];

        MemberLedgerCardService::ensureFontsExist();
        $mangalPath = str_replace('\\', '/', public_path('fonts/mangal.ttf'));
        $mangalbPath = str_replace('\\', '/', public_path('fonts/mangalb.ttf'));
        $aparajPath = str_replace('\\', '/', public_path('fonts/aparaj.ttf'));
        $aparajbPath = str_replace('\\', '/', public_path('fonts/aparajb.ttf'));

        return Pdf::loadView('pdf.receipt', compact('payment', 'society', 'mangalPath', 'mangalbPath', 'aparajPath', 'aparajbPath'))
            ->setPaper('a5', 'landscape');
    }
}
