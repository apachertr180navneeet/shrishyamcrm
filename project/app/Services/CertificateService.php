<?php

namespace App\Services;

use App\Models\Member;
use App\Models\SocietySetting;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    /**
     * Generate printable / downloadable PDF membership certificate matching the official certificate card
     */
    public static function generatePdf(int $memberId): \Barryvdh\DomPDF\PDF
    {
        $member = Member::with(['scheme', 'agent', 'nominees', 'ageSlab', 'documents'])->findOrFail($memberId);

        $society = [
            'name' => SocietySetting::getVal('society_name', 'Shri Shyam Welfare Society'),
            'name_hindi' => SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहिड़ी'),
            'reg_no' => SocietySetting::getVal('reg_no', 'COOP/2025/BALOTARA/500219'),
            'san_code' => $member->san_code ?: SocietySetting::getVal('san_prefix', '0878920000000014'),
            'address' => SocietySetting::getVal('address', 'ग्रा.पं. लोहिड़ी, पं.स. सिणधरी, जिला बालोतरा (राज.)'),
            'phone' => SocietySetting::getVal('phone', '9664090906, 9549635631, 9783049650'),
            'president' => SocietySetting::getVal('president_name', 'लादूराम'),
            'founder' => SocietySetting::getVal('founder_name', 'लादूराम'),
        ];

        $primaryNominee = $member->nominees->first();
        $nomineeName = $primaryNominee
            ? $primaryNominee->name . ($primaryNominee->relation ? ' (' . $primaryNominee->relation . ')' : '')
            : '-';

        $kishtRate = (float)($member->monthly_support_amount > 0
            ? $member->monthly_support_amount
            : ($member->ageSlab && (float)$member->ageSlab->support_amount > 0 ? $member->ageSlab->support_amount : 400.0));

        $photoDoc = $member->documents->where('document_type', 'Photo')->first();
        $photoPath = null;
        $rawPhoto = $member->photo ?: ($photoDoc ? $photoDoc->file_path : null);
        if (!empty($rawPhoto)) {
            if (str_starts_with($rawPhoto, 'data:image/')) {
                $photoPath = $rawPhoto;
            } else {
                $pathOnly = parse_url($rawPhoto, PHP_URL_PATH) ?: $rawPhoto;
                $storageRelative = ltrim(preg_replace('#^/storage/#i', '', $pathOnly), '/');

                $abs = public_path('storage/' . $storageRelative);
                if (file_exists($abs)) {
                    $photoPath = str_replace('\\', '/', $abs);
                } else {
                    $storageAbs = storage_path('app/public/' . $storageRelative);
                    if (file_exists($storageAbs)) {
                        $photoPath = str_replace('\\', '/', $storageAbs);
                    } elseif (file_exists(public_path(ltrim($pathOnly, '/')))) {
                        $photoPath = str_replace('\\', '/', public_path(ltrim($pathOnly, '/')));
                    } else {
                        $photoPath = $rawPhoto;
                    }
                }
            }
        }

        MemberLedgerCardService::ensureFontsExist();
        $mangalPath = str_replace('\\', '/', public_path('fonts/mangal.ttf'));
        $mangalbPath = str_replace('\\', '/', public_path('fonts/mangalb.ttf'));
        $aparajPath = str_replace('\\', '/', public_path('fonts/aparaj.ttf'));
        $aparajbPath = str_replace('\\', '/', public_path('fonts/aparajb.ttf'));
        $logoPath = file_exists(public_path('assets/society_logo.png'))
            ? str_replace('\\', '/', public_path('assets/society_logo.png'))
            : (file_exists(public_path('assets/society_logo.jpg')) ? str_replace('\\', '/', public_path('assets/society_logo.jpg')) : null);
        $signaturePath = file_exists(public_path('assets/signature_laduram.png'))
            ? str_replace('\\', '/', public_path('assets/signature_laduram.png'))
            : null;

        $rawSchemeName = $member->scheme ? ($member->scheme->name_hindi ?: $member->scheme->name) : 'विवाह योजना';
        $trimmedScheme = trim($rawSchemeName);
        if (str_ends_with($trimmedScheme, 'योजना प्रमाण पत्र')) {
            $schemeHeading = $trimmedScheme;
        } elseif (str_ends_with($trimmedScheme, 'प्रमाण पत्र')) {
            $schemeHeading = $trimmedScheme;
        } elseif (str_ends_with($trimmedScheme, 'योजना')) {
            $schemeHeading = $trimmedScheme . ' प्रमाण पत्र';
        } else {
            $schemeHeading = $trimmedScheme . ' योजना प्रमाण पत्र';
        }

        $isSeniorScheme = $member->scheme && (
            str_contains($member->scheme->name_hindi ?? '', 'बुजुर्ग') ||
            str_contains(strtolower($member->scheme->name ?? ''), 'senior')
        );
        $fatherSpouseLabel = $isSeniorScheme ? 'पिता/पति का नाम' : 'पिता का नाम';
        $policyNote = $isSeniorScheme
            ? '1–6 माह तक दुर्घटना होने पर 51000रु व 6माह बाद सदस्यानुसार भुगतान किया जायेगा'
            : '12 माह बाद शादी करने पर ही लाभ मिलेगा (समय अधिक लाभ अधिक)';

        $bgImageRelative = $isSeniorScheme
            ? 'assets/certificate_buzurg_bg.jpg'
            : 'assets/certificate_vivah_bg.jpg';
        $bgImagePath = str_replace('\\', '/', public_path($bgImageRelative));

        return Pdf::loadView('pdf.certificate', compact(
            'member',
            'society',
            'nomineeName',
            'kishtRate',
            'photoPath',
            'logoPath',
            'signaturePath',
            'schemeHeading',
            'policyNote',
            'isSeniorScheme',
            'fatherSpouseLabel',
            'bgImagePath',
            'mangalPath',
            'mangalbPath',
            'aparajPath',
            'aparajbPath'
        ))->setPaper('a4', 'landscape');
    }
}
