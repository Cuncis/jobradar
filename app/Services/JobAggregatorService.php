<?php

namespace App\Services;

use App\Models\CachedJob;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class JobAggregatorService
{
    // How long (minutes) before we consider DB results "stale"
    protected int $dbCacheTtl = 60;

    // How long (minutes) to keep results in Laravel's cache
    protected int $memCacheTtl = 30;

    public function __construct(
        protected AdzunaService $adzuna,
        protected TheMuseService $themuse,
        protected RemotiveService $remotive,
        protected JSearchService $jsearch,
        protected GlassdoorService $glassdoor,
    ) {
    }

    // -------------------------------------------------------
    // Main entry point — called by the controller
    // -------------------------------------------------------
    public function search(string $query): array
    {
        $cacheKey = 'job_search_' . md5(strtolower(trim($query)));

        // 1. Check memory/file cache first (fastest)
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 2. Check DB for recently cached results
        $dbResults = $this->getFromDatabase($query);

        if (!empty($dbResults)) {
            Cache::put($cacheKey, $dbResults, now()->addMinutes($this->memCacheTtl));
            return $dbResults;
        }

        // 3. Fetch fresh from all 4 APIs
        $freshJobs = $this->fetchFromAllSources($query);

        // 4. Save to DB (upsert — insert or update if exists)
        $this->saveToDatabase($freshJobs, $query);

        // 5. Re-read from DB so we get proper model data + IDs
        $results = $this->getFromDatabase($query, fresh: true);

        // 6. Store in cache
        Cache::put($cacheKey, $results, now()->addMinutes($this->memCacheTtl));

        return $results;
    }

    // -------------------------------------------------------
    // Find a single job by its DB primary key
    // -------------------------------------------------------
    public function findById(int $id): ?CachedJob
    {
        return CachedJob::find($id);
    }

    // -------------------------------------------------------
    // Delete cached jobs older than 24 hours
    // -------------------------------------------------------
    public function clearOldCache(): int
    {
        return CachedJob::where('updated_at', '<', Carbon::now()->subHours(24))->delete();
    }

    // -------------------------------------------------------
    // Private: Query DB for this search term
    // -------------------------------------------------------
    private function getFromDatabase(string $query, bool $fresh = false): array
    {
        $normalised = strtolower(trim($query));

        $queryBuilder = CachedJob::where('search_query', $normalised)
            ->orderByDesc('posted_at')
            ->limit(60);

        // If not forced fresh, only return rows updated recently
        if (!$fresh) {
            $queryBuilder->where(
                'updated_at',
                '>=',
                Carbon::now()->subMinutes($this->dbCacheTtl)
            );
        }

        return $queryBuilder->get()->toArray();
    }

    // -------------------------------------------------------
    // Private: Call all 4 services and merge results
    // -------------------------------------------------------
    private function fetchFromAllSources(string $query): array
    {
        $allJobs = [];

        // We wrap each call in its own try/catch so one
        // failing API never blocks the others
        $sources = [
            fn() => $this->adzuna->search($query),
            fn() => $this->themuse->search($query),
            fn() => $this->remotive->search($query),
            fn() => $this->jsearch->search($query),
            fn() => $this->glassdoor->search($query),
        ];

        foreach ($sources as $fetchJobs) {
            try {
                $jobs = $fetchJobs();
                $allJobs = array_merge($allJobs, $jobs);
            } catch (\Exception $e) {
                // Log and continue — partial results are better than none
                \Illuminate\Support\Facades\Log::warning('A job source failed: ' . $e->getMessage());
            }
        }

        return $this->deduplicate($allJobs);
    }

    // -------------------------------------------------------
    // Private: Remove duplicate jobs by external_id + source
    // -------------------------------------------------------
    private function deduplicate(array $jobs): array
    {
        $seen = [];
        $unique = [];

        foreach ($jobs as $job) {
            // Build a unique key from both fields
            $key = $job['source'] . '_' . $job['external_id'];

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $job;
            }
        }

        return $unique;
    }

    // -------------------------------------------------------
    // Private: Upsert jobs into DB
    // -------------------------------------------------------
    private function saveToDatabase(array $jobs, string $query): void
    {
        $normalised = strtolower(trim($query));

        foreach ($jobs as $job) {
            CachedJob::updateOrCreate(
                // Match on source + external_id + search_query (query-scoped unique key)
                [
                    'external_id' => $job['external_id'],
                    'source' => $job['source'],
                    'search_query' => $normalised,
                ],
                // Update/insert all other fields
                [
                    'title' => $job['title'],
                    'company' => $job['company'],
                    'location' => $job['location'],
                    'description' => $job['description'],
                    'salary_min' => $job['salary_min'],
                    'salary_max' => $job['salary_max'],
                    'salary_currency' => $job['salary_currency'],
                    'job_type' => $job['job_type'],
                    'category' => $job['category'],
                    'tags' => json_encode($job['tags'] ?? []),
                    'posted_at' => $job['posted_at'],
                    'external_url' => $job['external_url'],
                    'logo_url' => $job['logo_url'],
                    'raw_data' => json_encode($job['raw_data'] ?? []),
                ]
            );
        }
    }
}