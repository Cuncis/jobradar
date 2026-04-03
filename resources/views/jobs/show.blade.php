@extends('layouts.app')

@section('title', $job->title . ' at ' . $job->company . ' — JobRadar')

@section('content')

@php
    $colorMap = [
        'adzuna'       => '#00b8d9',
        'themuse'      => '#e8612c',
        'remotive'     => '#32c27d',
        'jsearch' => '#ff6154',
    ];
    $labelMap = [
        'adzuna'       => 'Adzuna',
        'themuse'      => 'The Muse',
        'remotive'     => 'Remotive',
        'jsearch' => 'JSearch',
    ];
    $iconMap = [
        'adzuna'       => '🔵',
        'themuse'      => '🟠',
        'remotive'     => '🟢',
        'jsearch' => '🔴',
    ];

    $color   = $colorMap[$job->source] ?? '#5b7fff';
    $label   = $labelMap[$job->source] ?? ucfirst($job->source);
    $icon    = $iconMap[$job->source]  ?? '🔘';
    $initial = strtoupper(substr($job->company ?? '?', 0, 1));
@endphp

<div class="max-w-6xl mx-auto px-6 py-8">

    {{-- ── Back link ── --}}
    <button onclick="history.back()"
            class="inline-flex items-center gap-2 text-muted text-sm
                   bg-transparent border-none cursor-pointer font-body
                   mb-7 transition-colors duration-150 hover:text-text">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to results
    </button>

    {{-- ── Two column layout ── --}}
    <div class="flex gap-7 items-start flex-col md:flex-row">

        {{-- ════════════════════════════
             LEFT — Main content
        ════════════════════════════ --}}
        <div class="flex-1 min-w-0">

            {{-- ── Job header card ── --}}
            <div class="bg-surface border border-border rounded-2xl p-6 mb-4"
                 style="border-left: 4px solid {{ $color }}">

                {{-- Logo + title --}}
                <div class="flex gap-4 items-start mb-6">

                    {{-- Company logo or initial --}}
                    <div class="w-16 h-16 rounded-2xl bg-surface2 border border-border
                                shrink-0 flex items-center justify-center
                                font-display font-black text-2xl overflow-hidden"
                         style="color: {{ $color }}">
                        @if($job->logo_url)
                            <img src="{{ $job->logo_url }}"
                                 alt="{{ $job->company }}"
                                 class="w-full h-full object-contain p-2"
                                 onerror="this.parentElement.textContent='{{ $initial }}'">
                        @else
                            {{ $initial }}
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <h1 class="font-display font-black text-2xl md:text-3xl
                                   tracking-tight leading-tight mb-1">
                            {{ $job->title }}
                        </h1>
                        <p class="text-muted font-medium text-base">
                            {{ $job->company }}
                        </p>
                    </div>
                </div>

                {{-- Meta grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-5
                            border-t border-border">

                    <div class="flex flex-col gap-1">
                        <span class="text-[0.65rem] font-bold uppercase
                                     tracking-[0.12em] text-muted">
                            Location
                        </span>
                        <span class="text-sm font-medium">
                            {{ $job->location ?? 'Not specified' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-[0.65rem] font-bold uppercase
                                     tracking-[0.12em] text-muted">
                            Job Type
                        </span>
                        <span class="text-sm font-medium">
                            {{ $job->job_type
                                ? str_replace('_', ' ', ucfirst($job->job_type))
                                : 'Not specified' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-[0.65rem] font-bold uppercase
                                     tracking-[0.12em] text-muted">
                            Salary
                        </span>
                        <span class="text-sm font-medium text-success">
                            {{ $job->salary_display }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-[0.65rem] font-bold uppercase
                                     tracking-[0.12em] text-muted">
                            Category
                        </span>
                        <span class="text-sm font-medium">
                            {{ $job->category ?? 'General' }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-[0.65rem] font-bold uppercase
                                     tracking-[0.12em] text-muted">
                            Posted
                        </span>
                        <span class="text-sm font-medium">
                            {{ $job->posted_ago }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-[0.65rem] font-bold uppercase
                                     tracking-[0.12em] text-muted">
                            Source
                        </span>
                        <span class="text-sm font-medium"
                              style="color: {{ $color }}">
                            {{ $label }}
                        </span>
                    </div>

                </div>
            </div>

            {{-- ── Description card ── --}}
            <div class="bg-surface border border-border rounded-2xl p-6">

                <p class="text-[0.68rem] font-bold uppercase tracking-[0.12em]
                          text-muted mb-5">
                    Job Description
                </p>

                @if($job->description)
                    {{-- nl2br preserves line breaks from the API response --}}
                    <div class="text-[0.92rem] leading-[1.85] text-[#c5cad9]
                                whitespace-pre-wrap break-words">
                        {{ $job->description }}
                    </div>
                @else
                    <p class="text-muted text-sm italic">
                        No description available for this listing.
                    </p>
                @endif

                {{-- Tags --}}
                @php
                    $tags = is_array($job->tags) ? $job->tags : (json_decode($job->tags, true) ?? []);
                @endphp
                @if(!empty($tags))
                    <div class="flex flex-wrap gap-2 mt-6 pt-6 border-t border-border">
                        @foreach($tags as $tag)
                            <span class="text-[0.75rem] px-3 py-1 rounded-full
                                         border border-border text-muted bg-surface2">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

        {{-- ════════════════════════════
             RIGHT — Sidebar
        ════════════════════════════ --}}
        <aside class="w-full md:w-72 shrink-0 flex flex-col gap-3 md:sticky md:top-[80px]">

            {{-- Source info card --}}
            <div class="bg-surface border border-border rounded-2xl p-5">

                {{-- Source header --}}
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-border">
                    <div class="w-10 h-10 rounded-xl bg-surface2 flex items-center
                                justify-center text-lg shrink-0">
                        {{ $icon }}
                    </div>
                    <div>
                        <p class="font-display font-bold text-sm"
                           style="color: {{ $color }}">
                            {{ $label }}
                        </p>
                        <p class="text-[0.72rem] text-muted">Job listing source</p>
                    </div>
                </div>

                {{-- Info rows --}}
                @foreach([
                    ['Company',  $job->company  ?? '—'],
                    ['Location', $job->location ?? '—'],
                    ['Type',     $job->job_type
                                    ? str_replace('_', ' ', ucfirst($job->job_type))
                                    : '—'],
                    ['Posted',   $job->posted_ago],
                ] as [$rowLabel, $rowValue])
                    <div class="flex items-center justify-between py-2.5
                                border-b border-border last:border-b-0">
                        <span class="text-xs text-muted">{{ $rowLabel }}</span>
                        <span class="text-xs font-medium text-right max-w-[55%]">
                            {{ $rowValue }}
                        </span>
                    </div>
                @endforeach

                {{-- Salary row with green color --}}
                <div class="flex items-center justify-between py-2.5 border-b border-border">
                    <span class="text-xs text-muted">Salary</span>
                    <span class="text-xs font-medium text-success">
                        {{ $job->salary_display }}
                    </span>
                </div>

                {{-- Job ID --}}
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-muted">Job ID</span>
                    <span class="text-[0.68rem] text-muted font-mono truncate max-w-[55%]"
                          title="{{ $job->external_id }}">
                        {{ Str::limit($job->external_id, 20) }}
                    </span>
                </div>

            </div>

            {{-- Info-only notice --}}
            <div class="bg-surface2 border border-border rounded-2xl p-4 text-center">
                <p class="text-base mb-1">ℹ️</p>
                <p class="text-xs font-bold text-text mb-1">Info Only View</p>
                <p class="text-[0.75rem] text-muted leading-relaxed">
                    This is a read-only listing. To apply, visit the original
                    posting on {{ $label }}.
                </p>
            </div>

            {{-- Find similar jobs --}}
            @php
                // Use the first word of the job title as the search keyword
                $similarKeyword = explode(' ', $job->title)[0];
            @endphp

            <a href="{{ route('home') }}?q={{ urlencode($similarKeyword) }}"
               class="flex items-center justify-center gap-2
                      bg-surface border border-border rounded-2xl p-4
                      text-sm font-medium text-text no-underline
                      transition-all duration-200
                      hover:border-brand hover:text-brand">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                Find similar jobs
            </a>

            {{-- Back to results --}}
            <button onclick="history.back()"
                    class="flex items-center justify-center gap-2
                           bg-transparent border border-border rounded-2xl p-4
                           text-sm text-muted cursor-pointer font-body w-full
                           transition-all duration-200 hover:text-text hover:bg-surface">
                ← Back to results
            </button>

        </aside>
    </div>
</div>

@endsection