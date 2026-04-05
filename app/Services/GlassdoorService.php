<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GlassdoorService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://glassdoor-real-time.p.rapidapi.com/jobs/search';

    public function __construct()
    {
        $this->apiKey = config('services.glassdoor.api_key', '');
    }

    public function search(string $query): array
    {
        if (empty($this->apiKey)) {
            return $this->mockData($query);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-rapidapi-host' => 'glassdoor-real-time.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get($this->baseUrl, [
                    'keyword' => $query,
                    'location' => '',
                    'page' => '1',
                ]);

            if (!$response->successful()) {
                Log::warning('Glassdoor API failed', ['status' => $response->status()]);
                return $this->mockData($query);
            }

            $jobs = $response->json()['jobs'] ?? $response->json()['data'] ?? [];

            return empty($jobs) ? $this->mockData($query) : $this->normalize($jobs);

        } catch (\Exception $e) {
            Log::error('GlassdoorService: ' . $e->getMessage());
            return $this->mockData($query);
        }
    }

    protected function normalize(array $jobs): array
    {
        return array_map(function ($job) {
            $salaryMin = $job['salaryMin'] ?? $job['salary_min'] ?? $job['payPeriodAdjustedPay']['p10'] ?? null;
            $salaryMax = $job['salaryMax'] ?? $job['salary_max'] ?? $job['payPeriodAdjustedPay']['p90'] ?? null;

            return [
                'external_id' => 'glassdoor_' . ($job['jobListingId'] ?? $job['id'] ?? uniqid()),
                'source' => 'glassdoor',
                'title' => $job['jobTitleText'] ?? $job['title'] ?? 'Unknown Title',
                'company' => $job['employerName'] ?? $job['company'] ?? 'Unknown Company',
                'location' => $job['locationName'] ?? $job['location'] ?? 'Not specified',
                'description' => strip_tags($job['jobDescriptionText'] ?? $job['description'] ?? ''),
                'salary_min' => $salaryMin ? (int) $salaryMin : null,
                'salary_max' => $salaryMax ? (int) $salaryMax : null,
                'salary_currency' => 'USD',
                'job_type' => $this->mapJobType($job['jobTypeKeys'][0] ?? $job['jobType'] ?? ''),
                'category' => $job['jobFunctions'][0]['name'] ?? $job['category'] ?? null,
                'tags' => array_values(array_filter([
                    ($job['isEasyApply'] ?? false) ? 'easy-apply' : null,
                    ($job['isRemote'] ?? ($job['locationType'] ?? '') === 'REMOTE') ? 'remote' : null,
                ])),
                'posted_at' => isset($job['listingDateLocalised']) || isset($job['postedDate'])
                    ? date('Y-m-d H:i:s', strtotime($job['listingDateLocalised'] ?? $job['postedDate']))
                    : now()->toDateTimeString(),
                'external_url' => $job['jobUrl'] ?? $job['applyUrl'] ?? $job['url'] ?? '#',
                'logo_url' => $job['squareLogo'] ?? $job['employerLogo'] ?? null,
                'raw_data' => [],
            ];
        }, $jobs);
    }

    protected function mapJobType(string $type): string
    {
        $map = [
            'fulltime' => 'full_time',
            'full_time' => 'full_time',
            'parttime' => 'part_time',
            'part_time' => 'part_time',
            'contract' => 'contract',
            'contractor' => 'contract',
            'intern' => 'internship',
            'temporary' => 'temporary',
        ];

        return $map[strtolower($type)] ?? 'full_time';
    }

    protected function mockData(string $query): array
    {
        $hash = md5(strtolower(trim($query)));

        return [
            [
                'external_id' => 'glassdoor_mock_a_' . $hash,
                'source' => 'glassdoor',
                'title' => ucwords($query) . ' Analyst',
                'company' => 'Insight Corp',
                'location' => 'San Francisco, CA',
                'description' => 'Join our team to analyze and optimize business processes. Collaborate with cross-functional teams to drive data-driven decisions.',
                'salary_min' => 90000,
                'salary_max' => 130000,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'Business Analysis',
                'tags' => ['easy-apply'],
                'posted_at' => now()->subDays(2)->toDateTimeString(),
                'external_url' => 'https://www.glassdoor.com/Job/jobs.htm',
                'logo_url' => null,
                'raw_data' => [],
            ],
            [
                'external_id' => 'glassdoor_mock_b_' . $hash,
                'source' => 'glassdoor',
                'title' => 'Senior ' . ucwords($query) . ' Developer',
                'company' => 'Nexus Solutions',
                'location' => 'Remote',
                'description' => 'Work remotely with a talented engineering team. You will design, build, and maintain high-performance applications.',
                'salary_min' => 120000,
                'salary_max' => 170000,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'Software Engineering',
                'tags' => ['remote', 'easy-apply'],
                'posted_at' => now()->subDays(3)->toDateTimeString(),
                'external_url' => 'https://www.glassdoor.com/Job/jobs.htm',
                'logo_url' => null,
                'raw_data' => [],
            ],
        ];
    }
}
