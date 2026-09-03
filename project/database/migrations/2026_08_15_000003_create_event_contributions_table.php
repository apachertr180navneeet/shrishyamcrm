<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create event_contributions table
        if (!Schema::hasTable('event_contributions')) {
            Schema::create('event_contributions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('marriage_events')->onDelete('cascade');
                $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
                $table->foreignId('scheme_id')->nullable()->constrained('schemes')->onDelete('set null');
                $table->string('event_name');
                $table->date('event_date');
                $table->string('member_name');
                $table->integer('member_age')->default(0);
                $table->string('age_slab')->nullable(); // e.g. "14–17 years"
                $table->decimal('contribution_amount', 10, 2)->default(0);
                $table->enum('payment_status', ['Pending', 'Paid', 'Partially Paid'])->default('Pending');
                $table->date('payment_date')->nullable();
                $table->foreignId('payment_id')->nullable();
                $table->string('receipt_no')->nullable();
                $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
                $table->timestamps();

                // Prevent accidental duplicate contribution records for SAME member + SAME event
                $table->unique(['event_id', 'member_id'], 'unique_event_member_contribution');
                $table->index(['event_id', 'payment_status']);
                $table->index(['member_id', 'payment_status']);
            });
        }

        // 2. Add event_id and event_contribution_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'event_id')) {
                $table->foreignId('event_id')->nullable()->after('member_id')->constrained('marriage_events')->onDelete('set null');
            }
            if (!Schema::hasColumn('payments', 'event_contribution_id')) {
                $table->foreignId('event_contribution_id')->nullable()->after('event_id')->constrained('event_contributions')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'event_contribution_id')) {
                $table->dropForeign(['event_contribution_id']);
                $table->dropColumn('event_contribution_id');
            }
            if (Schema::hasColumn('payments', 'event_id')) {
                $table->dropForeign(['event_id']);
                $table->dropColumn('event_id');
            }
        });

        Schema::dropIfExists('event_contributions');
    }
};
