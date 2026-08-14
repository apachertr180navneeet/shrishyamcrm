<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\NumberSeriesService;

class NumberSeriesSeeder extends Seeder
{
    public function run(): void
    {
        NumberSeriesService::seedStandardSeries();
    }
}
