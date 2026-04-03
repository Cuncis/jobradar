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
        Schema::create('cached_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index();        // ID from the source API
            $table->string('source', 50)->index();         // 'adzuna', 'remotive', etc.
            $table->string('title');                       // Job title
            $table->string('company')->nullable();         // Company name
            $table->string('location')->nullable();        // City, country, or "Remote"
            $table->longText('description')->nullable();   // Full job description
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 10)->default('USD');
            $table->string('job_type', 50)->nullable();    // full_time, remote, contract
            $table->string('category')->nullable();        // e.g. "IT Jobs", "Design"
            $table->json('tags')->nullable();              // Array of tag strings
            $table->timestamp('posted_at')->nullable();    // When it was posted on source
            $table->string('external_url')->nullable();    // Link to original posting
            $table->string('logo_url')->nullable();        // Company logo
            $table->json('raw_data')->nullable();          // Full API response (for debugging)
            $table->timestamps();                          // created_at, updated_at

            $table->unique(['external_id', 'source']);     // Ensure no duplicates from the same source
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cached_jobs');
    }
};
