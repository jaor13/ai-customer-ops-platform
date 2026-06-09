<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 500);
            $table->string('original_name', 500)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('version', 50)->default('1.0');
            $table->integer('chunk_count')->nullable();
            $table->jsonb('qdrant_ids')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_documents');
    }
};
