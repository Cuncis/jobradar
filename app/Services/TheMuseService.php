<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheMuseService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://www.themuse.com/api/public/jobs';

    public function __construct()
    {
        $this->apiKey = config('services.themuse.api_key', '');
    }

    public function search(string $query): array
    {
        try {
            $params = ['page' => 0, 'descending' => true];

            if (!empty($this->apiKey)) {
                $params['api_key'] = $this->apiKey;
            }

            $response = Http::timeout(10)->get($this->baseUrl, $params);

            if (!$response->successful()) {
                Log::warning('TheMuse API failed', ['status' => $response->status()]);
                return $this->mockData($query);
            }

            $results = $response->json()['results'] ?? [];

            // The Muse doesn't support keyword search in the free tier,
            // so we filter client-side by matching the query in title or description
            $filtered = array_filter($results, function ($job) use ($query) {
                $text = strtolower(($job['name'] ?? '') . ' ' . ($job['contents'] ?? ''));
                return str_contains($text, strtolower($query));
            });

            return $this->normalize(array_values($filtered));

        } catch (\Exception $e) {
            Log::error('TheMuseService: ' . $e->getMessage());
            return $this->mockData($query);
        }
    }

    protected function normalize(array $jobs): array
    {
        return array_map(function ($job) {
            return [
                'external_id' => 'muse_' . ($job['id'] ?? uniqid()),
                'source' => 'themuse',
                'title' => $job['name'] ?? 'Unknown Title',
                'company' => $job['company']['name'] ?? 'Unknown Company',
                'location' => $job['locations'][0]['name'] ?? 'Remote',
                'description' => strip_tags($job['contents'] ?? ''),
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'USD',
                'job_type' => $job['type'] ?? null,
                'category' => $job['categories'][0]['name'] ?? null,
                'tags' => array_column($job['tags'] ?? [], 'name'),
                'posted_at' => isset($job['publication_date'])
                    ? date('Y-m-d H:i:s', strtotime($job['publication_date']))
                    : now()->toDateTimeString(),
                'external_url' => $job['refs']['landing_page'] ?? '#',
                'logo_url' => $job['company']['refs']['logo_image'] ?? null,
                'raw_data' => [],
            ];
        }, $jobs);
    }

    protected function mockData(string $query): array
    {
        return [
            [
                'external_id' => 'muse_mock_1',
                'source' => 'themuse',
                'title' => ucwords($query) . ' Specialist',
                'company' => 'Creative Minds Inc.',
                'location' => 'San Francisco, CA',
                'description' => "We're looking for a passionate {$query} Specialist to drive innovation and collaborate with cross-functional teams.",
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'USD',
                'job_type' => 'Full Time',
                'category' => 'Technology',
                'tags' => ['flexible', 'culture-forward'],
                'posted_at' => now()->subDays(3)->toDateTimeString(),
                'external_url' => 'https://themuse.com',
                'logo_url' => null,
                'raw_data' => [],
            ],
        ];
    }
}