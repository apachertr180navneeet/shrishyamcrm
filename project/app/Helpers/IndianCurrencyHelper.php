<?php

namespace App\Helpers;

class IndianCurrencyHelper
{
    /**
     * Convert amount to Indian Rupees in words.
     * Example: 125500 -> "One Lakh Twenty Five Thousand Five Hundred Rupees Only"
     */
    public static function formatToWords(float|int|string|null $amount): string
    {
        $amount = (float)$amount;
        if ($amount <= 0) {
            return 'Zero Rupees Only';
        }

        $numberParts = explode('.', number_format($amount, 2, '.', ''));
        $wholeNumber = (int)$numberParts[0];
        $paise = isset($numberParts[1]) ? (int)$numberParts[1] : 0;

        $words = self::convertWholeNumberToWords($wholeNumber);
        $result = $words . ' Rupees';

        if ($paise > 0) {
            $paiseWords = self::convertWholeNumberToWords($paise);
            $result .= ' and ' . $paiseWords . ' Paise';
        }

        $result .= ' Only';

        return trim(preg_replace('/\s+/', ' ', $result));
    }

    private static function convertWholeNumberToWords(int $num): string
    {
        if ($num === 0) {
            return '';
        }

        $units = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];

        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        if ($num < 20) {
            return $units[$num];
        }

        if ($num < 100) {
            return $tens[(int)($num / 10)] . (($num % 10 !== 0) ? ' ' . $units[$num % 10] : '');
        }

        if ($num < 1000) {
            return $units[(int)($num / 100)] . ' Hundred' . (($num % 100 !== 0) ? ' ' . self::convertWholeNumberToWords($num % 100) : '');
        }

        if ($num < 100000) { // Thousands (1,000 - 99,999)
            return self::convertWholeNumberToWords((int)($num / 1000)) . ' Thousand' . (($num % 1000 !== 0) ? ' ' . self::convertWholeNumberToWords($num % 1000) : '');
        }

        if ($num < 10000000) { // Lakhs (1,00,000 - 99,99,999)
            return self::convertWholeNumberToWords((int)($num / 100000)) . ' Lakh' . (($num % 100000 !== 0) ? ' ' . self::convertWholeNumberToWords($num % 100000) : '');
        }

        // Crores (1,00,00,000+)
        return self::convertWholeNumberToWords((int)($num / 10000000)) . ' Crore' . (($num % 10000000 !== 0) ? ' ' . self::convertWholeNumberToWords($num % 10000000) : '');
    }

    /**
     * Format number in Indian currency style (e.g. 1,25,500.00)
     */
    public static function formatINR(float|int|string|null $amount, bool $includeSymbol = true, int $decimals = 2): string
    {
        $amount = (float)$amount;
        $isNegative = $amount < 0;
        $amount = abs($amount);

        $parts = explode('.', number_format($amount, $decimals, '.', ''));
        $whole = $parts[0];
        $decimal = isset($parts[1]) ? '.' . $parts[1] : '';

        if (strlen($whole) > 3) {
            $lastThree = substr($whole, -3);
            $remaining = substr($whole, 0, -3);
            $formattedRemaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
            $formatted = $formattedRemaining . ',' . $lastThree;
        } else {
            $formatted = $whole;
        }

        $res = ($isNegative ? '-' : '') . ($includeSymbol ? '₹' : '') . $formatted . ($decimals > 0 ? $decimal : '');
        return $res;
    }
}
