<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Improved RAG flow (see docs/11-rag-production-audit.md and
     * docs/12-rag-metadata-and-authority.md) makes `knowledge_documents` the
     * metadata source of truth. These columns enable authority-weighted
     * ranking, category filtering, active-version-only retrieval, effective
     * dating, and OCR/file provenance — the subset of which Qdrant mirrors
     * per chunk for fast filtering.
     *
     * The table is empty, so `doc_key` is added NOT NULL and `version` is
     * migrated from VARCHAR ('1.0') to INTEGER to match the canonical schema.
     */
    public function up(): void
    {
        // 1. Add new metadata columns.
        Schema::table('knowledge_documents', function (Blueprint $table) {
            // identity & versioning
            $table->string('doc_key');
            $table->boolean('is_active')->default(true);
            $table->string('status', 30)->default('active'); // active | superseded | archived | failed

            // classification & authority (prevents RAG confusion between similar docs)
            $table->string('category', 50)->default('general'); // pricing|faq|policy|sop|case_study|legal|product|general
            $table->decimal('authority_weight', 3, 2)->default(1.00); // 0.50 draft … 1.00 normal … 2.00 official

            // effective dating
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable(); // NULL = still in effect

            // file provenance
            $table->string('source_type', 30)->default('text'); // text|pdf|pdf_ocr|docx|md
            $table->string('mime_type', 100)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('checksum_sha256', 64)->nullable(); // dedupe identical uploads
            $table->integer('page_count')->nullable();
            $table->boolean('ocr_used')->default(false);

            // ingestion results (written back by n8n)
            $table->text('ingest_error')->nullable();

            // audit
            $table->timestamp('updated_at')->nullable();
        });

        // 2. Migrate `version` VARCHAR('1.0') -> INTEGER (table is empty, no backfill).
        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->dropColumn('version');
        });
        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->integer('version')->default(1);
        });

        // 3. Indexes for fast filtering + version uniqueness.
        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->unique(['doc_key', 'version']);
            $table->index(['doc_key', 'is_active'], 'idx_kd_active');
            $table->index('category', 'idx_kd_category');
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->dropUnique(['doc_key', 'version']);
            $table->dropIndex('idx_kd_active');
            $table->dropIndex('idx_kd_category');
        });

        // Restore `version` to its original VARCHAR form.
        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->dropColumn('version');
        });
        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->string('version', 50)->default('1.0');
        });

        Schema::table('knowledge_documents', function (Blueprint $table) {
            $table->dropColumn([
                'doc_key',
                'is_active',
                'status',
                'category',
                'authority_weight',
                'effective_from',
                'effective_to',
                'source_type',
                'mime_type',
                'file_size',
                'checksum_sha256',
                'page_count',
                'ocr_used',
                'ingest_error',
                'updated_at',
            ]);
        });
    }
};
