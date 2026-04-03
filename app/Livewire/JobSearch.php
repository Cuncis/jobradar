<?php

namespace App\Livewire;

use App\Services\JobAggregatorService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'JobRadar — Unified Job Search'])]
class JobSearch extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    #[Url]
    public string $source = '';

    #[Url]
    public string $type = '';

    public array $jobs = [];
    public array $sourceCounts = [];
    public bool $loading = false;
    public bool $searched = false;

    protected array $rules = [
        'query' => 'required|string|min:2|max:100',
        'source' => 'nullable|string|in:adzuna,themuse,remotive,ziprecruiter,',
        'type' => 'nullable|string|max:50',
    ];

    public function mount(): void
    {
        // If arriving via URL with ?q=..., run the search immediately
        if ($this->query !== '') {
            $this->runSearch();
        }
    }

    public function search(): void
    {
        $this->validate();
        $this->source = '';
        $this->type = '';
        $this->runSearch();
    }

    public function quickSearch(string $term): void
    {
        $this->query = $term;
        $this->source = '';
        $this->type = '';
        $this->runSearch();
    }

    public function filterSource(string $source): void
    {
        $this->source = $source;
        $this->runSearch();
    }

    public function filterType(string $type): void
    {
        $this->type = $type;
        $this->runSearch();
    }

    public function clearFilters(): void
    {
        $this->source = '';
        $this->type = '';
        $this->runSearch();
    }

    public function clearCache(): void
    {
        $aggregator = app(JobAggregatorService::class);
        $deleted = $aggregator->clearOldCache();
        session()->flash('success', "Cleared {$deleted} old cached jobs.");
    }

    private function runSearch(): void
    {
        if (trim($this->query) === '') {
            return;
        }

        $aggregator = app(JobAggregatorService::class);
        $allJobs = $aggregator->search(trim($this->query));

        // Build source counts from the full result set
        $this->sourceCounts = collect($allJobs)
            ->groupBy('source')
            ->map(fn($g) => $g->count())
            ->toArray();

        // Apply source filter
        if ($this->source !== '') {
            $allJobs = array_values(array_filter(
                $allJobs,
                fn($job) => ($job['source'] ?? '') === $this->source
            ));
        }

        // Apply type filter
        if ($this->type !== '') {
            $allJobs = array_values(array_filter(
                $allJobs,
                fn($job) => str_contains(
                    strtolower($job['job_type'] ?? ''),
                    strtolower($this->type)
                )
            ));
        }

        $this->jobs = $allJobs;
        $this->searched = true;
    }

    public function render()
    {
        return view('livewire.job-search');
    }
}
