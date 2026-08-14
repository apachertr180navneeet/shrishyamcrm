<?php

namespace App\Services;

use App\Models\Member;
use App\Models\SocietySetting;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    /**
     * Generate printable / downloadable PDF membership certificate
     */
    public static function generatePdf(int $memberId): \Barryvdh\DomPDF\PDF
    {
        $member = Member::with(['scheme', 'agent', 'nominees'])->findOrFail($memberId);

        $society = [
            'name' => SocietySetting::getVal('society_name', 'Shri Shyam Welfare Society'),
            'name_hindi' => SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहीकी'),
            'reg_no' => SocietySetting::getVal('reg_no', 'HR/019/2021/04582'),
            'address' => SocietySetting::getVal('address', 'Main Bazar, Lohki, District Mahendragarh, Haryana - 123001'),
            'president' => SocietySetting::getVal('president_name', 'Shri R. K. Sharma'),
            'secretary' => SocietySetting::getVal('secretary_name', 'Shri Mahesh Garg'),
        ];

        return Pdf::loadView('pdf.certificate', compact('member', 'society'))
            ->setPaper('a4', 'landscape');
    }
}
