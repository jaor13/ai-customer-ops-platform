<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('source', 100)->default('website');
            $table->string('status', 50)->default('new');
            $table->integer('score')->nullable();
            $table->string('category', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
