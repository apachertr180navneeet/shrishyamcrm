<?php

namespace App\Services;

use App\Models\NumberSeries;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NumberSeriesService
{
    /**
     * Get the next sequence value dynamically synchronized with the target database table.
     */
    public static function getTableSyncedValue(string $code, int $defaultInitial = 1001): int
    {
        $currentYear = Carbon::now()->format('Y');

        try {
            switch ($code) {
                case 'MEM':
                    $prefix = "MEM-{$currentYear}-";
                    $last = DB::table('members')
                        ->where('membership_no', 'like', "{$prefix}%")
                        ->orderByRaw('LENGTH(membership_no) DESC, membership_no DESC')
                        ->value('membership_no');

                    if ($last) {
                        $num = (int) str_replace($prefix, '', $last);
                        return max($num + 1, $defaultInitial);
                    }
                    return $defaultInitial;

                case 'REC':
                    $prefix = "REC-{$currentYear}-";
                    $last = DB::table('payments')
                        ->where('receipt_no', 'like', "{$prefix}%")
                        ->orderByRaw('LENGTH(receipt_no) DESC, receipt_no DESC')
                        ->value('receipt_no');

                    if ($last) {
                        $num = (int) str_replace($prefix, '', $last);
                        return max($num + 1, $defaultInitial);
                    }
                    return $defaultInitial;

                case 'AGT':
                    $prefix = "AGT-";
                    $last = DB::table('agents')
                        ->where('agent_code', 'like', "{$prefix}%")
                        ->orderByRaw('LENGTH(agent_code) DESC, agent_code DESC')
                        ->value('agent_code');

                    if ($last) {
                        $num = (int) str_replace($prefix, '', $last);
                        return max($num + 1, 1);
                    }
                    return 1;

                case 'EVT':
                    $prefix = "EVT-{$currentYear}-";
                    $last = DB::table('marriage_events')
                        ->where('event_code', 'like', "{$prefix}%")
                        ->orderByRaw('LENGTH(event_code) DESC, event_code DESC')
                        ->value('event_code');

                    if ($last) {
                        $num = (int) str_replace($prefix, '', $last);
                        return max($num + 1, 1);
                    }
                    return 1;

                case 'PAY':
                    $prefix = "PAY-{$currentYear}-";
                    $last = DB::table('payouts')
                        ->where('payout_no', 'like', "{$prefix}%")
                        ->orderByRaw('LENGTH(payout_no) DESC, payout_no DESC')
                        ->value('payout_no');

                    if ($last) {
                        $num = (int) str_replace($prefix, '', $last);
                        return max($num + 1, 1);
                    }
                    return 1;

                default:
                    return $defaultInitial;
            }
        } catch (\Throwable $e) {
            return $defaultInitial;
        }
    }

    /**
     * Generate next sequential number in a thread-safe, concurrency-safe manner.
     *
     * @param string $code (MEM, REC, CRT, AGT, EVT, PAY, TXN)
     * @param array $defaults Default configuration if series doesn't exist
     * @return string e.g. MEM-2026-1001, REC-2026-5001, CRT-2026-8001
     */
    public static function getNextNumber(string $code, array $defaults = []): string
    {
        return DB::transaction(function () use ($code, $defaults) {
            $series = NumberSeries::where('code', $code)->lockForUpdate()->first();
            $currentYear = Carbon::now()->format('Y');
            $initialVal = $defaults['initial_value'] ?? 1001;
            $tableSynced = self::getTableSyncedValue($code, $initialVal);

            if (!$series) {
                $prefix = $defaults['prefix'] ?? "{$code}-{$currentYear}-";
                $padding = $defaults['padding'] ?? 4;
                $currentVal = max($initialVal, $tableSynced);

                $series = NumberSeries::create([
                    'code' => $code,
                    'prefix' => $prefix,
                    'current_value' => $currentVal + 1,
                    'padding' => $padding,
                    'year_format' => 'YYYY',
                    'description' => "Sequence for {$code}",
                ]);

                $numberPart = str_pad($currentVal, $series->padding, '0', STR_PAD_LEFT);
                return str_replace('YYYY', $currentYear, $series->prefix) . $numberPart;
            }

            // Always ensure series keeps pace with actual max value in the table
            $currentVal = max($series->current_value, $tableSynced);
            $prefix = str_replace('YYYY', $currentYear, $series->prefix);
            $numberPart = str_pad($currentVal, $series->padding, '0', STR_PAD_LEFT);
            $finalNumber = $prefix . $numberPart;

            $series->update(['current_value' => $currentVal + 1]);

            return $finalNumber;
        });
    }

    /**
     * Anticipate (without consuming) the next sequential number for display purposes.
     */
    public static function peekNextNumber(string $code, array $defaults = []): string
    {
        $series = NumberSeries::where('code', $code)->first();
        $currentYear = Carbon::now()->format('Y');
        $initialVal = $defaults['initial_value'] ?? 1001;
        $padding = $defaults['padding'] ?? 4;
        $tableSynced = self::getTableSyncedValue($code, $initialVal);

        if (!$series) {
            $prefix = $defaults['prefix'] ?? "{$code}-{$currentYear}-";
            $currentVal = max($initialVal, $tableSynced);
            $numberPart = str_pad($currentVal, $padding, '0', STR_PAD_LEFT);
            return str_replace('YYYY', $currentYear, $prefix) . $numberPart;
        }

        $currentVal = max($series->current_value, $tableSynced);
        $prefix = str_replace('YYYY', $currentYear, $series->prefix);
        $numberPart = str_pad($currentVal, $series->padding, '0', STR_PAD_LEFT);
        return $prefix . $numberPart;
    }

    /**
     * Initialize standard series if not present
     */
    public static function seedStandardSeries(): void
    {
        $currentYear = Carbon::now()->format('Y');

        $standards = [
            'MEM' => ['prefix' => "MEM-{$currentYear}-", 'initial' => 1001, 'pad' => 4],
            'REC' => ['prefix' => "REC-{$currentYear}-", 'initial' => 5001, 'pad' => 4],
            'CRT' => ['prefix' => "CRT-{$currentYear}-", 'initial' => 8001, 'pad' => 4],
            'AGT' => ['prefix' => "AGT-", 'initial' => 1, 'pad' => 3],
            'EVT' => ['prefix' => "EVT-{$currentYear}-", 'initial' => 1, 'pad' => 2],
            'PAY' => ['prefix' => "PAY-{$currentYear}-", 'initial' => 1, 'pad' => 3],
            'TXN' => ['prefix' => "TXN-{$currentYear}-", 'initial' => 10001, 'pad' => 5],
        ];

        foreach ($standards as $code => $cfg) {
            NumberSeries::firstOrCreate(
                ['code' => $code],
                [
                    'prefix' => $cfg['prefix'],
                    'current_value' => $cfg['initial'],
                    'padding' => $cfg['pad'],
                    'year_format' => 'YYYY',
                    'description' => "Standard sequence for {$code}",
                ]
            );
        }
    }
}
