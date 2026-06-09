<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->text('draft_body');
            $table->text('edited_body')->nullable();
            $table->jsonb('context_sources')->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_queue');
    }
};
