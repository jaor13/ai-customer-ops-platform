<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->string('event_type', 100);
            $table->text('description')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('lead_id')->references('id')->on('leads');
            $table->index('customer_id');
            $table->index('lead_id');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
