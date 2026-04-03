<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JSearchService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://jsearch.p.rapidapi.com/search';

    public function __construct()
    {
        $this->apiKey = config('services.jsearch.api_key', '');
    }

    public function search(string $query): array
    {
        if (empty($this->apiKey)) {
            return $this->mockData($query);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-rapidapi-host' => 'jsearch.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get($this->baseUrl, [
                    'query' => $query,
                    'page' => '1',
                    'num_pages' => '1',
                    'date_posted' => 'all',
                ]);

            if (!$response->successful()) {
                Log::warning('JSearch API failed', ['status' => $response->status()]);
                return $this->mockData($query);
            }

            $jobs = $response->json()['data'] ?? [];

            return empty($jobs) ? $this->mockData($query) : $this->normalize($jobs);

        } catch (\Exception $e) {
            Log::error('JSearchService: ' . $e->getMessage());
            return $this->mockData($query);
        }
    }

    protected function normalize(array $jobs): array
    {
        return array_map(function ($job) {
            // Parse salary — JSearch returns min/max or a single estimate
            $salaryMin = $job['job_min_salary'] ?? null;
            $salaryMax = $job['job_max_salary'] ?? null;
            $currency = strtoupper($job['job_salary_currency'] ?? 'USD');

            // Map JSearch employment type to our internal format
            $typeMap = [
                'FULLTIME' => 'full_time',
                'PARTTIME' => 'part_time',
                'CONTRACTOR' => 'contract',
                'INTERN' => 'internship',
            ];
            $rawType = strtoupper($job['job_employment_type'] ?? '');
            $jobType = $typeMap[$rawType] ?? strtolower($rawType ?: 'full_time');

            return [
                'external_id' => 'jsearch_' . ($job['job_id'] ?? uniqid()),
                'source' => 'jsearch',
                'title' => $job['job_title'] ?? 'Unknown Title',
                'company' => $job['employer_name'] ?? 'Unknown Company',
                'location' => trim(
                    implode(', ', array_filter([
                        $job['job_city'] ?? null,
                        $job['job_state'] ?? null,
                        $job['job_country'] ?? null,
                    ]))
                ) ?: ($job['job_is_remote'] ? 'Remote' : 'Not specified'),
                'description' => strip_tags($job['job_description'] ?? ''),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'salary_currency' => $currency,
                'job_type' => $jobType,
                'category' => $job['job_occupational_categories'][0] ?? null,
                'tags' => array_filter([
                    $job['job_is_remote'] ? 'remote' : null,
                    strtolower($job['job_required_experience']['required_experience_in_months'] ?? 0) > 0
                    ? 'experienced' : null,
                ]),
                'posted_at' => isset($job['job_posted_at_datetime_utc'])
                    ? date('Y-m-d H:i:s', strtotime($job['job_posted_at_datetime_utc']))
                    : now()->toDateTimeString(),
                'external_url' => $job['job_apply_link'] ?? $job['job_google_link'] ?? '#',
                'logo_url' => $job['employer_logo'] ?? null,
                'raw_data' => [],
            ];
        }, $jobs);
    }

    protected function mockData(string $query): array
    {
        return [
            [
                'external_id' => 'jsearch_mock_1',
                'source' => 'jsearch',
                'title' => ucwords($query) . ' Engineer',
                'company' => 'TechScale Inc.',
                'location' => 'Austin, TX',
                'description' => 'Build and scale distributed systems. Work with a world-class engineering team on mission-critical infrastructure used by millions.',
                'salary_min' => 110000,
                'salary_max' => 160000,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'Software Engineering',
                'tags' => ['onsite', 'experienced'],
                'posted_at' => now()->subDays(1)->toDateTimeString(),
                'external_url' => 'https://rapidapi.com/letscrape-6bRBa3QguO5/api/jsearch',
                'logo_url' => null,
                'raw_data' => [],
            ],
            [
                'external_id' => 'jsearch_mock_2',
                'source' => 'jsearch',
                'title' => 'Senior ' . ucwords($query) . ' Developer',
                'company' => 'CloudNative Co.',
                'location' => 'Remote',
                'description' => 'Join a fully remote team. Own features end to end, mentor junior devs, and help shape our technical roadmap.',
                'salary_min' => 130000,
                'salary_max' => 180000,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'Software Engineering',
                'tags' => ['remote', 'senior'],
                'posted_at' => now()->subHours(6)->toDateTimeString(),
                'external_url' => 'https://rapidapi.com/letscrape-6bRBa3QguO5/api/jsearch',
                'logo_url' => null,
                'raw_data' => [],
            ],
        ];
    }
}
