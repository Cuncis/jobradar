<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachedJob extends Model
{
    protected $table = 'cached_jobs';

    // These fields can be mass-assigned (safe to fill from arrays)
    protected $fillable = [
        'external_id',
        'source',
        'title',
        'company',
        'location',
        'description',
        'salary_min',
        'salary_max',
        'salary_currency',
        'job_type',
        'category',
        'tags',
        'posted_at',
        'external_url',
        'logo_url',
        'raw_data',
    ];

    protected $cast = [
        'tags' => 'array', // Cast JSON to array
        'raw_data' => 'array', // Cast JSON to array
        'posted_at' => 'datetime', // Cast to Carbon instance
    ];

    // -------------------------
    // Accessor: Salary display
    // -------------------------
    public function getSalaryDisplayAttribute(): string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return 'Not specified';
        }

        $symbol = match ($this->salary_currency ?? 'USD') {
            'GBP' => '£',
            'EUR' => '€',
            default => '$',
        };

        if ($this->salary_min && $this->salary_max) {
            return $symbol . number_format($this->salary_min)
                . ' – '
                . $symbol . number_format($this->salary_max);
        }

        return $symbol . number_format($this->salary_min ?? $this->salary_max);
    }

    // -------------------------
    // Accessor: Human-readable posted time
    // -------------------------
    public function getPostedAgoAttribute(): string
    {
        if (!$this->posted_at)
            return 'Recently';
        return $this->posted_at->diffForHumans(); // e.g. "3 days ago"
    }

    // -------------------------
    // Accessor: Source display name
    // -------------------------
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'adzuna' => 'Adzuna',
            'themuse' => 'The Muse',
            'remotive' => 'Remotive',
            'ziprecruiter' => 'ZipRecruiter',
            default => ucfirst($this->source),
        };
    }

    // -------------------------
    // Accessor: Source brand color
    // -------------------------
    public function getSourceColorAttribute(): string
    {
        return match ($this->source) {
            'adzuna' => '#00b8d9',
            'themuse' => '#e8612c',
            'remotive' => '#32c27d',
            'ziprecruiter' => '#4a90e2',
            default => '#5b7fff',
        };
    }
}
