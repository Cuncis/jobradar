<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cached_jobs', function (Blueprint $table) {
            // Drop the old 2-column unique (external_id, source)
            $table->dropUnique(['external_id', 'source']);
            // New 3-column unique: same job can be cached under different queries
            $table->unique(['external_id', 'source', 'search_query'], 'cached_jobs_external_source_query_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cached_jobs', function (Blueprint $table) {
            $table->dropUnique('cached_jobs_external_source_query_unique');
            $table->unique(['external_id', 'source']);
        });
    }
};
