<?php

namespace App\Services;

use App\Models\WhatsAppLog;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use App\Models\SocietySetting;
use Carbon\Carbon;

class WhatsAppService
{
    /**
     * Build payment receipt message text and direct WhatsApp click URL
     */
    public static function getReceiptMessage(Payment $payment): array
    {
        $societyName = SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहीकी');
        $member = $payment->member;
        $cleanMobile = preg_replace('/[^0-9]/', '', $member->mobile ?? '');
        if (strlen($cleanMobile) === 10) {
            $cleanMobile = '91' . $cleanMobile;
        }

        $schemeName = $member->scheme ? $member->scheme->name_hindi : 'कल्याण योजना';
        $due = $member->pending_amount;

        $msg = "🙏 *{$societyName}* 🙏\n\n";
        $msg .= "आदरणीय *{$member->full_name}* जी,\n";
        $msg .= "आपकी सदस्यता संख्या: *{$member->membership_no}*\n";
        $msg .= "योजना: *{$schemeName}*\n\n";
        $msg .= "✅ *भुगतान रसीद विवरण:*\n";
        $msg .= "रसीद नं: *{$payment->receipt_no}*\n";
        if ($payment->event) {
            $msg .= "कार्यक्रम: *{$payment->event->title}*\n";
        }
        $msg .= "दिनांक: *{$payment->payment_date->format('d-m-Y')}*\n";
        $msg .= "प्राप्त राशि: *₹" . number_format($payment->amount, 2) . "*\n";
        $msg .= "भुगतान माध्यम: *{$payment->payment_mode}*\n";
        $msg .= "शेष बकाया: *₹" . number_format($due, 2) . "*\n\n";
        $msg .= "सोसायटी में आपके अमूल्य सहयोग के लिए धन्यवाद।\n";
        $msg .= "हेल्पलाइन: " . SocietySetting::getVal('phone', '+91 98290 12345');

        $encodedMsg = urlencode($msg);
        $url = "https://api.whatsapp.com/send?phone={$cleanMobile}&text={$encodedMsg}";

        return [
            'mobile' => $cleanMobile,
            'message' => $msg,
            'url' => $url,
        ];
    }

    /**
     * Build pending due reminder message text
     */
    public static function getDueReminderMessage(Member $member): array
    {
        $societyName = SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहीकी');
        $cleanMobile = preg_replace('/[^0-9]/', '', $member->mobile ?? '');
        if (strlen($cleanMobile) === 10) {
            $cleanMobile = '91' . $cleanMobile;
        }

        $msg = "🙏 *{$societyName}* 🙏\n\n";
        $msg .= "प्रिय सदस्य *{$member->full_name}* जी,\n";
        $msg .= "सदस्यता संख्या: *{$member->membership_no}*\n\n";
        $msg .= "आपके खाते में कुल बकाया राशि: *₹" . number_format($member->pending_amount, 2) . "* है।\n";
        $msg .= "कृपया समय पर सहयोग राशि जमा करवाकर समाज सेवा में भागीदार बनें।\n\n";
        $msg .= "सम्पर्क: " . SocietySetting::getVal('phone', '+91 98290 12345');

        $encodedMsg = urlencode($msg);
        $url = "https://api.whatsapp.com/send?phone={$cleanMobile}&text={$encodedMsg}";

        return [
            'mobile' => $cleanMobile,
            'message' => $msg,
            'url' => $url,
        ];
    }

    /**
     * Build Agent Login Credentials message text and direct WhatsApp click URL
     */
    public static function getAgentCredentialsMessage(Agent $agent, ?string $password = null): array
    {
        $societyName = SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहीड़ी');
        $rawMobile = $agent->mobile ?: ($agent->user ? $agent->user->phone : '');
        $cleanMobile = preg_replace('/[^0-9]/', '', $rawMobile);
        if (strlen($cleanMobile) === 10) {
            $cleanMobile = '91' . $cleanMobile;
        }

        $loginUrl = route('admin.login');
        $username = $agent->mobile ?: ($agent->user ? $agent->user->email : $agent->agent_code);
        $email = $agent->email ?: ($agent->user ? $agent->user->email : '-');

        $msg = "🙏 *{$societyName}* 🙏\n\n";
        $msg .= "आदरणीय कार्यकर्ता *{$agent->name}* जी,\n";
        $msg .= "सोसायटी एजेंट पोर्टल में आपका स्वागत है। आपके पोर्टल का लॉगिन विवरण निम्नलिखित है:\n\n";
        $msg .= "🔗 *लॉगिन पोर्टल लिंक:* {$loginUrl}\n";
        $msg .= "👤 *यूजरनेम (मोबाइल/ईमेल):* *{$username}*\n";
        if ($email && $email !== $username && $email !== '-') {
            $msg .= "📧 *ईमेल ID:* {$email}\n";
        }
        if (!empty($password)) {
            $msg .= "🔑 *पासवर्ड (Password):* *{$password}*\n";
        } else {
            $msg .= "🔑 *पासवर्ड (Password):* (पंजीकृत पासवर्ड)\n";
        }
        $msg .= "🏢 *एजेंट कोड:* *{$agent->agent_code}*\n";
        $msg .= "📍 *कार्यक्षेत्र (जिला):* {$agent->district}\n\n";
        $msg .= "कृपया ऊपर दिए गए पोर्टल लिंक पर जाकर लॉगिन करें और अपने क्षेत्र के सदस्यों का रिकॉर्ड देखें व सहयोग राशि दर्ज करें।\n\n";
        $msg .= "सहयोग/सहायता हेल्पलाइन: " . SocietySetting::getVal('phone', '9664090906');

        $encodedMsg = urlencode($msg);
        $url = "https://api.whatsapp.com/send?phone={$cleanMobile}&text={$encodedMsg}";

        return [
            'mobile' => $cleanMobile,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'login_url' => $loginUrl,
            'message' => $msg,
            'url' => $url,
        ];
    }

    /**
     * Log sent WhatsApp communication
     */
    public static function logMessage(?int $memberId, string $recipientName, string $mobile, string $messageType, string $body, string $status = 'Sent'): WhatsAppLog
    {
        return WhatsAppLog::create([
            'member_id' => $memberId,
            'recipient_name' => $recipientName,
            'mobile' => $mobile,
            'message_type' => $messageType,
            'message_body' => $body,
            'status' => $status,
            'sent_at' => Carbon::now(),
        ]);
    }
}
