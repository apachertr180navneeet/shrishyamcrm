<?php

namespace App\Services;

use App\Models\NumberSeries;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NumberSeriesService
{
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

            if (!$series) {
                $prefix = $defaults['prefix'] ?? "{$code}-{$currentYear}-";
                $initialVal = $defaults['initial_value'] ?? 1001;
                $padding = $defaults['padding'] ?? 4;

                $series = NumberSeries::create([
                    'code' => $code,
                    'prefix' => $prefix,
                    'current_value' => $initialVal,
                    'padding' => $padding,
                    'year_format' => 'YYYY',
                    'description' => "Sequence for {$code}",
                ]);

                $numberPart = str_pad($series->current_value, $series->padding, '0', STR_PAD_LEFT);
                $finalNumber = str_replace('YYYY', $currentYear, $series->prefix) . $numberPart;

                // Increment for next usage
                $series->increment('current_value');

                return $finalNumber;
            }

            // If prefix has year placeholder or static year
            $prefix = str_replace('YYYY', $currentYear, $series->prefix);
            $numberPart = str_pad($series->current_value, $series->padding, '0', STR_PAD_LEFT);
            $finalNumber = $prefix . $numberPart;

            $series->increment('current_value');

            return $finalNumber;
        });
    }

    /**
     * Anticipate (without consuming) the next sequential number for display purposes.
     */
    public static function peekNextNumber(string $code, array $defaults = []): string
    {
        $series = NumberSeries::where('code', $code)->lockForUpdate()->first();

        $currentYear = Carbon::now()->format('Y');

        if (!$series) {
            $prefix = $defaults['prefix'] ?? "{$code}-{$currentYear}-";
            $initialVal = $defaults['initial_value'] ?? 1001;
            $padding = $defaults['padding'] ?? 4;
            $numberPart = str_pad($initialVal, $padding, '0', STR_PAD_LEFT);
            return str_replace('YYYY', $currentYear, $prefix) . $numberPart;
        }

        $prefix = str_replace('YYYY', $currentYear, $series->prefix);
        $numberPart = str_pad($series->current_value, $series->padding, '0', STR_PAD_LEFT);
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
