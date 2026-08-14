<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. super_admin, admin, agent, data_entry, accountant
            $table->string('display_name'); // e.g. Super Admin
            $table->string('display_name_hindi')->nullable(); // e.g. सुपर एडमिन
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Permissions Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. member.view, member.create, payment.create
            $table->string('display_name');
            $table->string('group')->default('general'); // members, payments, schemes, events, reports, settings, users
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role-Permission Pivot
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Role-User Pivot
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // 5. Update Users Table with agent link & role id if not present
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->after('role')->constrained('agents')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('agent_id')->constrained('roles')->onDelete('set null');
            }
        });

        // 6. Number Series Table for atomic, concurrency-safe sequential code generation
        Schema::create('number_series', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // MEM, REC, CRT, AGT, EVT, PAY
            $table->string('prefix'); // MEM-2026-, REC-2026-, etc.
            $table->unsignedBigInteger('current_value')->default(1000);
            $table->integer('padding')->default(4);
            $table->string('year_format')->nullable(); // YYYY
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 7. Financial Ledgers Table (Double Entry / Debit-Credit Tracking)
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique(); // TXN-2026-XXXX
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->date('transaction_date');
            $table->enum('entry_type', ['Joining Fee', 'Monthly Due', 'Event Billing', 'Payment', 'Adjustment', 'Refund', 'Payout']);
            $table->string('description');
            $table->decimal('debit', 12, 2)->default(0); // Charge/Due increases balance
            $table->decimal('credit', 12, 2)->default(0); // Payment decreases balance
            $table->decimal('running_balance', 12, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['member_id', 'transaction_date']);
        });

        // 8. Member Documents Table
        Schema::create('member_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->enum('document_type', ['Photo', 'Aadhaar', 'Identity Proof', 'Address Proof', 'Other'])->default('Photo');
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 9. Agent Commissions Table
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('cascade');
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->decimal('collection_amount', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(5.00); // 5%
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->enum('status', ['Earned', 'Paid', 'Cancelled'])->default('Earned');
            $table->date('payout_date')->nullable();
            $table->timestamps();
        });

        // 10. Consolidated Event Billings Table (to prevent duplicate monthly billing runs)
        Schema::create('event_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained('marriage_events')->onDelete('set null');
            $table->string('billing_month', 7); // e.g. 2026-07
            $table->string('month_name', 20); // July 2026
            $table->foreignId('scheme_id')->nullable()->constrained('schemes')->onDelete('set null');
            $table->integer('events_count')->default(1);
            $table->decimal('rate_per_event', 10, 2)->default(200);
            $table->decimal('total_per_member', 10, 2)->default(200);
            $table->integer('billed_members_count')->default(0);
            $table->decimal('total_billing_amount', 12, 2)->default(0);
            $table->date('billing_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->unique(['event_id', 'billing_month', 'scheme_id'], 'evt_month_scheme_unique');
        });

        // 11. Certificates Table
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_no')->unique(); // e.g. CRT-2026-8001
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('scheme_id')->nullable()->constrained('schemes')->onDelete('set null');
            $table->date('issue_date');
            $table->string('authorized_by')->default('President / Secretary');
            $table->string('verification_code')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // 12. Audit Logs Table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, updated, deleted, login, logout, approved, disbursed, export
            $table->string('module'); // members, payments, payouts, schemes, settings, users
            $table->string('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 13. Enhance Nominees Table
        Schema::table('nominees', function (Blueprint $table) {
            if (!Schema::hasColumn('nominees', 'father_husband_name')) {
                $table->string('father_husband_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('nominees', 'address')) {
                $table->text('address')->nullable()->after('aadhaar_no');
            }
            if (!Schema::hasColumn('nominees', 'percentage')) {
                $table->decimal('percentage', 5, 2)->default(100.00)->after('priority');
            }
        });

        // 14. Enhance Agents Table
        Schema::table('agents', function (Blueprint $table) {
            if (!Schema::hasColumn('agents', 'name_hindi')) {
                $table->string('name_hindi')->nullable()->after('name');
            }
            if (!Schema::hasColumn('agents', 'father_husband_name')) {
                $table->string('father_husband_name')->nullable()->after('name_hindi');
            }
            if (!Schema::hasColumn('agents', 'alternate_mobile')) {
                $table->string('alternate_mobile', 20)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('agents', 'state')) {
                $table->string('state')->default('Haryana')->after('district');
            }
            if (!Schema::hasColumn('agents', 'pincode')) {
                $table->string('pincode', 10)->nullable()->after('state');
            }
            if (!Schema::hasColumn('agents', 'photo')) {
                $table->string('photo')->nullable()->after('status');
            }
            if (!Schema::hasColumn('agents', 'notes')) {
                $table->text('notes')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('agents', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('notes');
            }
        });

        // 15. Enhance Payouts Table
        Schema::table('payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('payouts', 'member_id')) {
                $table->foreignId('member_id')->nullable()->after('event_id')->constrained('members')->onDelete('set null');
            }
            if (!Schema::hasColumn('payouts', 'scheme_id')) {
                $table->foreignId('scheme_id')->nullable()->after('member_id')->constrained('schemes')->onDelete('set null');
            }
            if (!Schema::hasColumn('payouts', 'payout_type')) {
                $table->enum('payout_type', ['Marriage Assistance', 'Elderly Death Claim', 'Welfare Assistance', 'Other Approved Assistance'])->default('Marriage Assistance')->after('scheme_id');
            }
            if (!Schema::hasColumn('payouts', 'disbursed_by')) {
                $table->string('disbursed_by')->nullable()->after('approved_by');
            }
        });

        // 16. Enhance Marriage Events Table
        Schema::table('marriage_events', function (Blueprint $table) {
            if (!Schema::hasColumn('marriage_events', 'event_type')) {
                $table->enum('event_type', ['Marriage Support', 'Welfare Distribution', 'Health Camp', 'Other Society Events'])->default('Marriage Support')->after('title');
            }
            if (!Schema::hasColumn('marriage_events', 'scheme_id')) {
                $table->foreignId('scheme_id')->nullable()->after('member_id')->constrained('schemes')->onDelete('set null');
            }
            if (!Schema::hasColumn('marriage_events', 'rate_per_event')) {
                $table->decimal('rate_per_event', 10, 2)->default(200.00)->after('beneficiary_payout_amount');
            }
            if (!Schema::hasColumn('marriage_events', 'number_of_members')) {
                $table->integer('number_of_members')->default(0)->after('rate_per_event');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('event_billings');
        Schema::dropIfExists('agent_commissions');
        Schema::dropIfExists('member_documents');
        Schema::dropIfExists('ledgers');
        Schema::dropIfExists('number_series');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
