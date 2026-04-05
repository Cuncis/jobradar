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
            $table->string('search_query')->nullable()->index()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cached_jobs', function (Blueprint $table) {
            $table->dropColumn('search_query');
        });
    }
};
