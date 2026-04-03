<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemotiveService
{
    protected string $baseUrl = 'https://remotive.com/api/remote-jobs';

    // No constructor needed — Remotive is fully public, no API key

    public function search(string $query): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'search' => $query,
                'limit' => 20,
            ]);

            if (!$response->successful()) {
                Log::warning('Remotive API failed', ['status' => $response->status()]);
                return $this->mockData($query);
            }

            $normalized = $this->normalize($response->json()['jobs'] ?? []);
            return empty($normalized) ? $this->mockData($query) : $normalized;

        } catch (\Exception $e) {
            Log::error('RemotiveService: ' . $e->getMessage());
            return $this->mockData($query);
        }
    }

    protected function normalize(array $jobs): array
    {
        return array_map(function ($job) {
            return [
                'external_id' => 'rem_' . ($job['id'] ?? uniqid()),
                'source' => 'remotive',
                'title' => $job['title'] ?? 'Unknown Title',
                'company' => $job['company_name'] ?? 'Unknown Company',
                'location' => $job['candidate_required_location'] ?? 'Worldwide',
                'description' => strip_tags($job['description'] ?? ''),
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'USD',
                'job_type' => $job['job_type'] ?? 'remote',
                'category' => $job['category'] ?? null,
                'tags' => $job['tags'] ?? [],
                'posted_at' => isset($job['publication_date'])
                    ? date('Y-m-d H:i:s', strtotime($job['publication_date']))
                    : now()->toDateTimeString(),
                'external_url' => $job['url'] ?? '#',
                'logo_url' => $job['company_logo'] ?? null,
                'raw_data' => [],
            ];
        }, $jobs);
    }

    protected function mockData(string $query): array
    {
        return [
            [
                'external_id' => 'rem_mock_1',
                'source' => 'remotive',
                'title' => 'Remote ' . ucwords($query) . ' Lead',
                'company' => 'DistributedWork Co.',
                'location' => 'Worldwide',
                'description' => "100% remote. Work from anywhere. Async-first culture. You'll lead a distributed team across multiple time zones.",
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'USD',
                'job_type' => 'full_time',
                'category' => 'Software Development',
                'tags' => ['remote', 'async', 'worldwide'],
                'posted_at' => now()->subHours(12)->toDateTimeString(),
                'external_url' => 'https://remotive.com',
                'logo_url' => null,
                'raw_data' => [],
            ],
        ];
    }
}