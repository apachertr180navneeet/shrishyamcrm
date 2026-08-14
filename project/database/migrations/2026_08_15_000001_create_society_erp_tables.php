<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schemes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. SENIOR, MARRIAGE
            $table->string('name'); // Senior Welfare Scheme
            $table->string('name_hindi'); // बुजुर्ग सम्मान योजना
            $table->string('type')->default('Welfare'); // Senior Welfare Scheme, Marriage Scheme
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('age_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes')->onDelete('cascade');
            $table->string('slab_code')->nullable(); // e.g. SLAB-S1
            $table->integer('min_age')->default(0);
            $table->integer('max_age')->default(120);
            $table->decimal('joining_amount', 10, 2)->default(0);
            $table->decimal('support_amount', 10, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('agent_code')->unique(); // e.g. AGT-001
            $table->string('name');
            $table->string('code')->nullable(); // AGT01
            $table->string('mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('district')->nullable(); // Mahendragarh, Bhiwani, Rewari, etc.
            $table->text('address')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00); // 5%
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('membership_no')->unique(); // e.g. MEM-2026-1001
            $table->string('full_name');
            $table->string('father_spouse_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->default('Male');
            $table->date('dob')->nullable();
            $table->integer('age')->default(0);
            $table->string('mobile', 20)->nullable();
            $table->string('gotra')->nullable();
            $table->string('caste')->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->default('Haryana');
            $table->string('pincode', 10)->nullable();
            $table->string('aadhaar_no', 20)->nullable();
            $table->foreignId('scheme_id')->nullable()->constrained('schemes')->onDelete('set null');
            $table->foreignId('age_slab_id')->nullable()->constrained('age_slabs')->onDelete('set null');
            $table->decimal('joining_amount', 10, 2)->default(0);
            $table->decimal('monthly_support_amount', 10, 2)->default(0);
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->date('joining_date')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Suspended'])->default('Active');
            $table->string('photo')->nullable();
            $table->decimal('pending_amount', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('nominees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('name');
            $table->string('relation')->nullable(); // Spouse, Son, Daughter, Father, Mother, etc.
            $table->string('mobile', 20)->nullable();
            $table->string('aadhaar_no', 20)->nullable();
            $table->integer('priority')->default(1); // 1 = Primary, 2 = Secondary
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique(); // e.g. REC-2026-5001
            $table->string('san_code')->nullable(); // e.g. SAN-LOH-001
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('payment_type', ['Joining Fee', 'Monthly Support', 'Event Contribution', 'Special Donation'])->default('Monthly Support');
            $table->enum('payment_mode', ['Cash', 'UPI', 'Bank Transfer', 'Cheque'])->default('UPI');
            $table->string('reference_no')->nullable(); // UTR / Transaction No
            $table->string('month_year')->nullable(); // e.g. Aug 2026
            $table->date('payment_date');
            $table->enum('status', ['Verified', 'Pending', 'Failed'])->default('Verified');
            $table->string('collected_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('marriage_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique(); // e.g. EVT-2026-01
            $table->string('title');
            $table->string('girl_name');
            $table->string('father_name')->nullable();
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->date('event_date');
            $table->string('venue')->nullable();
            $table->decimal('target_amount', 10, 2)->default(51000);
            $table->decimal('collected_amount', 10, 2)->default(0);
            $table->decimal('beneficiary_payout_amount', 10, 2)->default(51000);
            $table->enum('status', ['Upcoming', 'Active', 'Completed'])->default('Upcoming');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_no')->unique(); // e.g. PAY-2026-001
            $table->foreignId('event_id')->nullable()->constrained('marriage_events')->onDelete('set null');
            $table->string('beneficiary_name');
            $table->string('relation')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('payout_date');
            $table->string('approved_by')->nullable();
            $table->enum('payment_mode', ['Bank Transfer', 'Cheque', 'Cash', 'UPI'])->default('Bank Transfer');
            $table->string('transaction_ref')->nullable();
            $table->enum('status', ['Eligible', 'Pending Approval', 'Approved', 'Disbursed', 'Rejected'])->default('Pending Approval');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->string('recipient_name');
            $table->string('mobile', 20);
            $table->string('message_type')->default('Receipt'); // Receipt, Event Reminder, Welcome, Due Alert
            $table->text('message_body');
            $table->enum('status', ['Sent', 'Queued', 'Failed'])->default('Sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('society_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('society_settings');
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('marriage_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('nominees');
        Schema::dropIfExists('members');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('age_slabs');
        Schema::dropIfExists('schemes');
    }
};
