<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marriage_events') && Schema::hasColumn('marriage_events', 'event_type')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `marriage_events` MODIFY COLUMN `event_type` VARCHAR(191) NULL DEFAULT 'Marriage Support'");
            } else {
                Schema::table('marriage_events', function (Blueprint $table) {
                    $table->string('event_type', 191)->nullable()->default('Marriage Support')->change();
                });
            }
        }

        if (Schema::hasTable('payouts') && Schema::hasColumn('payouts', 'payout_type')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `payouts` MODIFY COLUMN `payout_type` VARCHAR(191) NULL DEFAULT 'Marriage Assistance'");
            } else {
                Schema::table('payouts', function (Blueprint $table) {
                    $table->string('payout_type', 191)->nullable()->default('Marriage Assistance')->change();
                });
            }
        }
    }

    public function down(): void
    {
        // Revert not required as string is broader than enum
    }
};
