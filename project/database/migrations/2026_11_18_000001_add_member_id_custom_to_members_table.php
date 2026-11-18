<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop old member_id column if present
            if (Schema::hasColumn('members', 'member_id')) {
                // Drop index if exists
                try {
                    $table->dropIndex('members_member_id_index');
                } catch (\Throwable $e) {}
                $table->dropColumn('member_id');
            }

            // Add custom party memberID column
            if (!Schema::hasColumn('members', 'memberID')) {
                $table->string('memberID', 100)->nullable()->unique()->after('membership_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'memberID')) {
                $table->dropUnique(['memberID']);
                $table->dropColumn('memberID');
            }
        });
    }
};
