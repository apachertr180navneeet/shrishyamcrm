<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Performance indexes for payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'payment_date'], 'idx_payments_status_date');
            $table->index('payment_date', 'idx_payments_date');
            $table->index('payment_type', 'idx_payments_type');
            $table->index('month_year', 'idx_payments_month_year');
            $table->index(['agent_id', 'status'], 'idx_payments_agent_status');
        });

        // 2. Performance indexes for members table
        Schema::table('members', function (Blueprint $table) {
            $table->index('status', 'idx_members_status');
            $table->index(['status', 'scheme_id'], 'idx_members_status_scheme');
            $table->index(['status', 'agent_id'], 'idx_members_status_agent');
            $table->index('joining_date', 'idx_members_joining_date');
            $table->index('mobile', 'idx_members_mobile');
            $table->index('aadhaar_no', 'idx_members_aadhaar');
        });

        // 3. Performance indexes for nominees table
        Schema::table('nominees', function (Blueprint $table) {
            $table->index(['member_id', 'relation'], 'idx_nominees_member_relation');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_status_date');
            $table->dropIndex('idx_payments_date');
            $table->dropIndex('idx_payments_type');
            $table->dropIndex('idx_payments_month_year');
            $table->dropIndex('idx_payments_agent_status');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('idx_members_status');
            $table->dropIndex('idx_members_status_scheme');
            $table->dropIndex('idx_members_status_agent');
            $table->dropIndex('idx_members_joining_date');
            $table->dropIndex('idx_members_mobile');
            $table->dropIndex('idx_members_aadhaar');
        });

        Schema::table('nominees', function (Blueprint $table) {
            $table->dropIndex('idx_nominees_member_relation');
        });
    }
};
