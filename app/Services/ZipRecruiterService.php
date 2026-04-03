<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZipRecruiterService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.ziprecruiter.com/jobs/v1';

    public function __construct()
    {
        $this->apiKey = config('services.ziprecruiter.api_key', '');
    }

    public function search(string $query): array
    {
        if (empty($this->apiKey)) {
            return $this->mockData($query);
        }

        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'search' => $query,
                'api_key' => $this->apiKey,
                'page' => 1,
                'per_page' => 20,
            ]);

            if (!$response->successful()) {
                Log::warning('ZipRecruiter API failed', ['status' => $response->status()]);
                return [];
            }

            return $this->normalize($response->json()['jobs'] ?? []);

        } catch (\Exception $e) {
            Log::error('ZipRecruiterService: ' . $e->getMessage());
            return $this->mockData($query);
        }
    }

    protected function normalize(array $jobs): array
    {
        return array_map(function ($job) {
            return [
                'external_id' => 'zip_' . ($job['id'] ?? uniqid()),
                'source' => 'ziprecruiter',
                'title' => $job['name'] ?? 'Unknown Title',
                'company' => $job['hiring_company']['name'] ?? 'Unknown Company',
                'location' => trim(($job['city'] ?? '') . ', ' . ($job['state'] ?? ''), ', '),
                'description' => strip_tags($job['snippet'] ?? ''),
                'salary_min' => $job['salary_min_annual'] ?? null,
                'salary_max' => $job['salary_max_annual'] ?? null,
                'salary_currency' => 'USD',
                'job_type' => $job['employment_type'] ?? null,
                'category' => null,
                'tags' => [],
                'posted_at' => isset($job['posted_time'])
                    ? date('Y-m-d H:i:s', strtotime($job['posted_time']))
                    : now()->toDateTimeString(),
                'external_url' => $job['url'] ?? '#',
                'logo_url' => $job['hiring_company']['logo'] ?? null,
                'raw_data' => [],
            ];
        }, $jobs);
    }

    protected function mockData(string $query): array
    {
        return [
            [
                'external_id' => 'zip_mock_1',
                'source' => 'ziprecruiter',
                'title' => ucwords($query) . ' Manager',
                'company' => 'Enterprise Solutions LLC',
                'location' => 'Chicago, IL',
                'description' => "Manage a team of 5-10 professionals, drive strategy, report to VP of Engineering. PMP certification a plus.",
                'salary_min' => 95000,
                'salary_max' => 140000,
                'salary_currency' => 'USD',
                'job_type' => 'FULL_TIME',
                'category' => 'Management',
                'tags' => ['management', 'onsite'],
                'posted_at' => now()->subDays(4)->toDateTimeString(),
                'external_url' => 'https://ziprecruiter.com',
                'logo_url' => null,
                'raw_data' => [],
            ],
            [
                'external_id' => 'zip_mock_2',
                'source' => 'ziprecruiter',
                'title' => 'Junior ' . ucwords($query) . ' Developer',
                'company' => 'StartupHub Inc.',
                'location' => 'Miami, FL',
                'description' => "Great entry-level opportunity. Ship real features from day one and grow fast. Recent graduates welcome.",
                'salary_min' => 60000,
                'salary_max' => 85000,
                'salary_currency' => 'USD',
                'job_type' => 'FULL_TIME',
                'category' => 'Technology',
                'tags' => ['junior', 'entry-level', 'hybrid'],
                'posted_at' => now()->subHours(6)->toDateTimeString(),
                'external_url' => 'https://ziprecruiter.com',
                'logo_url' => null,
                'raw_data' => [],
            ],
        ];
    }
}