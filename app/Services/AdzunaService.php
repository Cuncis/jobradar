<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class AdzunaService
{
    protected string $appId;
    protected string $appKey;
    protected string $baseUrl = 'https://api.adzuna.com/v1/api/jobs';
    protected string $country;

    public function __construct()
    {
        $this->appId = config('services.adzuna.app_id', '');
        $this->appKey = config('services.adzuna.app_key', '');
        $this->country = config('services.adzuna.country', 'us');
    }

    public function search(string $query): array
    {
        // Fall back to mock data if no API key configured
        if (empty($this->appId) || empty($this->appKey)) {
            return $this->mockData($query);
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$this->country}/search/1", [
                'app_id' => $this->appId,
                'app_key' => $this->appKey,
                'what' => $query,
                'results_per_page' => 20,
            ]);

            if (!$response->successful()) {
                Log::warning('Adzuna API failed', ['status' => $response->status()]);
                return [];
            }

            return $this->normalize($response->json()['results'] ?? []);

        } catch (\Exception $e) {
            Log::error('AdzunaService: ' . $e->getMessage());
            return $this->mockData($query);
        }
    }

    protected function normalize(array $jobs): array
    {
        return array_map(function ($job) {
            return [
                'external_id' => (string) ($job['id'] ?? uniqid('adz_')),
                'source' => 'adzuna',
                'title' => $job['title'] ?? 'Unknown Title',
                'company' => $job['company']['display_name'] ?? 'Unknown Company',
                'location' => $job['location']['display_name'] ?? 'Remote',
                'description' => strip_tags($job['description'] ?? ''),
                'salary_min' => $job['salary_min'] ?? null,
                'salary_max' => $job['salary_max'] ?? null,
                'salary_currency' => 'USD',
                'job_type' => $job['contract_time'] ?? null,
                'category' => $job['category']['label'] ?? null,
                'tags' => [],
                'posted_at' => isset($job['created'])
                    ? date('Y-m-d H:i:s', strtotime($job['created']))
                    : now()->toDateTimeString(),
                'external_url' => $job['redirect_url'] ?? '#',
                'logo_url' => null,
                'raw_data' => [],
            ];
        }, $jobs);
    }

    protected function mockData(string $query): array
    {
        return [
            [
                'external_id' => 'adz_mock_1',
                'source' => 'adzuna',
                'title' => ucwords($query) . ' Engineer',
                'company' => 'TechCorp Global',
                'location' => 'New York, NY',
                'description' => "We are looking for a talented {$query} professional. Requirements: 3+ years experience, strong communication skills, team player attitude.",
                'salary_min' => 90000,
                'salary_max' => 130000,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'IT Jobs',
                'tags' => ['remote-friendly', 'senior'],
                'posted_at' => now()->subDays(2)->toDateTimeString(),
                'external_url' => 'https://adzuna.com',
                'logo_url' => null,
                'raw_data' => [],
            ],
            [
                'external_id' => 'adz_mock_2',
                'source' => 'adzuna',
                'title' => 'Senior ' . ucwords($query) . ' Developer',
                'company' => 'Innovate Solutions',
                'location' => 'Austin, TX',
                'description' => "Lead projects, mentor junior developers, collaborate with stakeholders. 5+ years experience required.",
                'salary_min' => 120000,
                'salary_max' => 160000,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'IT Jobs',
                'tags' => ['leadership', 'hybrid'],
                'posted_at' => now()->subDays(1)->toDateTimeString(),
                'external_url' => 'https://adzuna.com',
                'logo_url' => null,
                'raw_data' => [],
            ],
        ];
    }

}