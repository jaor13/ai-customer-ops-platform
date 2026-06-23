<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Improved Phase 1 (lead-welcome flow) added two draft sources to the
     * approval queue. These columns let one queue + one sender workflow
     * handle both `ticket_reply` (Phase 2) and `lead_welcome` (Phase 1).
     */
    public function up(): void
    {
        Schema::table('approval_queue', function (Blueprint $table) {
            $table->string('type', 50)->default('ticket_reply')->after('id');
            $table->foreignId('lead_id')->nullable()->after('ticket_id')->constrained('leads');
            $table->string('recipient_email')->nullable()->after('customer_id');
            $table->string('subject', 500)->nullable()->after('recipient_email');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('approval_queue', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'recipient_email', 'subject']);
        });
    }
};
