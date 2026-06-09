<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_queue_id')->nullable()->constrained('approval_queue');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('to_email');
            $table->string('subject', 500)->nullable();
            $table->text('body')->nullable();
            $table->string('status', 50)->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
