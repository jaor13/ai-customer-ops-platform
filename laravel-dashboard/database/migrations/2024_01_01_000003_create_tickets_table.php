<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('subject', 500)->nullable();
            $table->text('body')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('priority', 50)->default('MEDIUM');
            $table->string('status', 50)->default('open');
            $table->string('source_email')->nullable();
            $table->string('gmail_thread_id')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('priority');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
