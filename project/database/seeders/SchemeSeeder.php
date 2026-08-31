<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scheme;
use App\Models\AgeSlab;

class SchemeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Senior Welfare Scheme (बुजुर्ग सम्मान योजना)
        $senior = Scheme::updateOrCreate(
            ['code' => 'SENIOR'],
            [
                'name' => 'Senior Welfare Scheme',
                'name_hindi' => 'बुजुर्ग सम्मान योजना',
                'type' => 'Senior Welfare Scheme',
                'status' => 'Active',
                'effective_from' => '2021-01-01',
                'effective_to' => '2030-12-31',
                'description' => 'Monthly financial support and welfare scheme for elderly society members.',
            ]
        );

        $seniorSlabs = [
            ['slab_code' => 'SLAB-S1', 'min_age' => 18, 'max_age' => 40, 'joining_amount' => 1100, 'support_amount' => 200],
            ['slab_code' => 'SLAB-S2', 'min_age' => 41, 'max_age' => 59, 'joining_amount' => 1500, 'support_amount' => 300],
            ['slab_code' => 'SLAB-S3', 'min_age' => 60, 'max_age' => 74, 'joining_amount' => 2000, 'support_amount' => 400],
            ['slab_code' => 'SLAB-S4', 'min_age' => 75, 'max_age' => 120, 'joining_amount' => 2500, 'support_amount' => 500],
        ];

        foreach ($seniorSlabs as $slab) {
            AgeSlab::updateOrCreate(
                ['scheme_id' => $senior->id, 'slab_code' => $slab['slab_code']],
                array_merge($slab, ['scheme_id' => $senior->id, 'status' => 'Active', 'effective_from' => '2021-01-01', 'effective_to' => '2030-12-31'])
            );
        }

        // 2. Marriage Scheme (विवाह योजना)
        $marriage = Scheme::updateOrCreate(
            ['code' => 'MARRIAGE'],
            [
                'name' => 'Marriage Scheme (Kanyadaan/Gotra)',
                'name_hindi' => 'विवाह (कन्यादान/गौत्र) योजना',
                'type' => 'Marriage Scheme',
                'status' => 'Active',
                'effective_from' => '2021-01-01',
                'effective_to' => '2030-12-31',
                'description' => 'Financial assistance scheme for girl child marriage and family welfare support.',
            ]
        );

        $marriageSlabs = [
            ['slab_code' => 'SLAB-M1', 'min_age' => 0, 'max_age' => 5, 'joining_amount' => 1100, 'support_amount' => 100],
            ['slab_code' => 'SLAB-M2', 'min_age' => 6, 'max_age' => 9, 'joining_amount' => 1100, 'support_amount' => 200],
            ['slab_code' => 'SLAB-M3', 'min_age' => 10, 'max_age' => 13, 'joining_amount' => 2000, 'support_amount' => 300],
            ['slab_code' => 'SLAB-M4', 'min_age' => 14, 'max_age' => 16, 'joining_amount' => 2500, 'support_amount' => 400],
            ['slab_code' => 'SLAB-M5', 'min_age' => 17, 'max_age' => 120, 'joining_amount' => 2500, 'support_amount' => 500],
        ];

        foreach ($marriageSlabs as $slab) {
            AgeSlab::updateOrCreate(
                ['scheme_id' => $marriage->id, 'slab_code' => $slab['slab_code']],
                array_merge($slab, ['scheme_id' => $marriage->id, 'status' => 'Active', 'effective_from' => '2021-01-01', 'effective_to' => '2030-12-31'])
            );
        }
    }
}
