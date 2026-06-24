<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | n8n — workflow orchestrator
    |--------------------------------------------------------------------------
    |
    | Laravel is the ingestion front door: it validates, extracts text (+OCR)
    | and POSTs ready text + metadata to the n8n "03 Knowledge Base - Ingestion"
    | webhook, which chunks, embeds (Ollama) and upserts to Qdrant. The secret
    | is sent as a shared header so the webhook can reject unauthenticated calls.
    | See docs/12-rag-metadata-and-authority.md §6.
    |
    */

    'n8n' => [
        'ingest_url' => env('N8N_INGEST_URL'),
        'secret' => env('N8N_WEBHOOK_SECRET'),
        'timeout' => (int) env('N8N_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Knowledge base storage & ingestion limits
    |--------------------------------------------------------------------------
    |
    | Original uploads are kept on the configured disk (S3 in production) so
    | documents can be re-processed or re-OCR'd later. OCR tooling is optional;
    | when the binaries are unavailable, scanned-PDF fallback is skipped.
    |
    */

    'knowledge_base' => [
        'disk' => env('KB_DISK', 's3'),
        'max_upload_kb' => (int) env('KB_MAX_UPLOAD_KB', 10240), // 10 MB
        'ocr_enabled' => (bool) env('KB_OCR_ENABLED', true),
        'ocrmypdf_path' => env('OCRMYPDF_PATH', 'ocrmypdf'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Qdrant — vector store (internal Docker network)
    |--------------------------------------------------------------------------
    |
    | Laravel owns the document lifecycle, so it talks to Qdrant directly to
    | delete, supersede, and patch chunk payloads — no n8n hop needed. n8n is
    | only responsible for ingestion (chunk → embed → upsert).
    |
    */

    'qdrant' => [
        'host' => env('QDRANT_HOST', 'http://qdrant:6333'),
        'collection' => env('QDRANT_COLLECTION', 'ai_crm_knowledge'),
        'timeout' => (int) env('QDRANT_TIMEOUT', 15),
    ],

];
