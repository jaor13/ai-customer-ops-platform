<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill NULL created_at/updated_at on leads and tickets.
     *
     * These rows were inserted by n8n workflows that didn't set timestamps.
     * Without valid timestamps, dashboard charts and "this week" stats show
     * incorrect (zero) values.
     */
    public function up(): void
    {
        // Set NULL created_at to NOW() for leads
        DB::table('leads')
            ->whereNull('created_at')
            ->update(['created_at' => now(), 'updated_at' => now()]);

        // Set NULL created_at to NOW() for tickets
        DB::table('tickets')
            ->whereNull('created_at')
            ->update(['created_at' => now(), 'updated_at' => now()]);
    }

    /**
     * This is a data-fix migration — not reversible in a meaningful way.
     */
    public function down(): void
    {
        // Intentionally left empty — we cannot know which rows had NULL before.
    }
};
