<?php

namespace App\Http\Controllers;

use App\Services\JobAggregatorService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // Laravel automatically injects JobAggregatorService here
    // because we registered it as a singleton in AppServiceProvider
    public function __construct(
        protected JobAggregatorService $aggregator
    ) {
    }

    // -------------------------------------------------------
    // GET /
    // Show the homepage with the search form
    // -------------------------------------------------------
    public function index()
    {
        return view('jobs.index');
    }

    // -------------------------------------------------------
    // GET /jobs/search?q=laravel&source=adzuna&type=remote
    // Run the search and show results
    // -------------------------------------------------------
    public function search(Request $request)
    {
        // Validate input — q is required, others optional
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'source' => 'nullable|string|in:adzuna,themuse,remotive,ziprecruiter',
            'type' => 'nullable|string',
        ]);

        $query = trim($request->input('q'));
        $filters = $request->only(['source', 'type']);

        // Get all jobs from aggregator (cached or fresh)
        $jobs = $this->aggregator->search($query);

        // Apply source filter if selected
        if (!empty($filters['source'])) {
            $jobs = array_filter(
                $jobs,
                fn($job) => ($job['source'] ?? '') === $filters['source']
            );
            $jobs = array_values($jobs); // re-index after filter
        }

        // Apply job type filter if selected
        if (!empty($filters['type'])) {
            $jobs = array_filter(
                $jobs,
                fn($job) => str_contains(
                    strtolower($job['job_type'] ?? ''),
                    strtolower($filters['type'])
                )
            );
            $jobs = array_values($jobs);
        }

        // Count how many results per source (for sidebar pills)
        // We do this AFTER filtering so counts reflect filtered results
        $sources = collect($jobs)
            ->groupBy('source')
            ->map(fn($group) => $group->count());

        return view('jobs.results', compact('jobs', 'query', 'sources', 'filters'));
    }

    // -------------------------------------------------------
    // GET /jobs/{id}
    // Show detail page for a single job
    // -------------------------------------------------------
    public function show(int $id)
    {
        $job = $this->aggregator->findById($id);

        // If job doesn't exist in our DB, show 404
        if (!$job) {
            abort(404, 'Job not found.');
        }

        return view('jobs.show', compact('job'));
    }

    // -------------------------------------------------------
    // POST /jobs/cache/clear
    // Delete cached jobs older than 24 hours
    // -------------------------------------------------------
    public function clearCache()
    {
        $deleted = $this->aggregator->clearOldCache();

        return back()->with('success', "Cleared {$deleted} old cached jobs.");
    }
}