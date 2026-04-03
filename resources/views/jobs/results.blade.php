@extends('layouts.app')

@section('title', '"' . $query . '" Jobs — JobRadar')

@section('content')

{{-- ── Sticky search bar (sits just below the nav at top:60px) ── --}}
<div class="sticky top-15 z-40 border-b border-border bg-bg/90 backdrop-blur-lg py-3">
    <div class="max-w-6xl mx-auto px-6">
        <form action="{{ route('jobs.search') }}" method="GET">

            {{-- Preserve active filters when re-searching --}}
            @if(!empty($filters['source']))
                <input type="hidden" name="source" value="{{ $filters['source'] }}">
            @endif

            <div class="flex items-center gap-3 bg-surface border border-border
                        rounded-2xl px-5 py-2 max-w-2xl
                        transition-all duration-200
                        focus-within:border-brand focus-within:shadow-[0_0_0_3px_rgba(91,127,255,0.12)]">

                <svg class="text-muted shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>

                <input
                    type="text"
                    name="q"
                    value="{{ $query }}"
                    class="flex-1 bg-transparent border-none outline-none
                           text-text placeholder:text-muted text-base py-2.5 font-body"
                    placeholder="Search jobs..."
                    autocomplete="off"
                >

                <button type="submit"
                        class="shrink-0 bg-gradient-to-br from-brand to-brand2
                               text-white font-display font-bold text-sm
                               px-6 py-3 rounded-xl tracking-wide cursor-pointer
                               transition-all duration-150
                               hover:opacity-90 hover:-translate-y-px active:translate-y-0">
                    Search
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Main layout ── --}}
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex gap-7 items-start">

        {{-- ════════════════════════
             SIDEBAR
        ════════════════════════ --}}
        <aside class="w-56 shrink-0 sticky top-[120px] hidden md:flex flex-col gap-3">

            {{-- Source filter --}}
            <div class="bg-surface border border-border rounded-2xl p-4">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-muted mb-3">
                    Source
                </p>

                {{-- All sources --}}
                <a href="{{ route('jobs.search', ['q' => $query]) }}"
                   class="flex items-center justify-between px-3 py-2 rounded-xl text-sm no-underline
                          transition-colors duration-150
                          {{ empty($filters['source']) ? 'text-brand bg-brand/8' : 'text-text hover:bg-surface2' }}">
                    <span>All Sources</span>
                    <span class="text-[0.7rem] text-muted bg-surface2 px-2 py-0.5 rounded-full">
                        {{ collect($sources)->sum() }}
                    </span>
                </a>

                @php
                    $sourceMap = [
                        'adzuna'       => ['Adzuna',       'var(--color-adzuna)'],
                        'themuse'      => ['The Muse',     'var(--color-themuse)'],
                        'remotive'     => ['Remotive',     'var(--color-remotive)'],
                        'ziprecruiter' => ['ZipRecruiter', 'var(--color-ziprecruiter)'],
                    ];
                @endphp

                @foreach($sourceMap as $key => [$label, $color])
                    @php $count = $sources[$key] ?? 0; @endphp
                    @if($count > 0)
                        <a href="{{ route('jobs.search', ['q' => $query, 'source' => $key]) }}"
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-sm
                                  no-underline transition-colors duration-150 mt-0.5
                                  {{ ($filters['source'] ?? '') === $key ? 'bg-surface2' : 'text-text hover:bg-surface2' }}"
                           style="{{ ($filters['source'] ?? '') === $key ? "color:{$color}" : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $color }}"></span>
                                {{ $label }}
                            </span>
                            <span class="text-[0.7rem] text-muted bg-surface2 px-2 py-0.5 rounded-full">
                                {{ $count }}
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>

            {{-- Job type filter --}}
            <div class="bg-surface border border-border rounded-2xl p-4">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-muted mb-3">
                    Job Type
                </p>

                @foreach([
                    'full_time' => 'Full Time',
                    'part_time' => 'Part Time',
                    'remote'    => 'Remote',
                    'contract'  => 'Contract',
                ] as $type => $typeLabel)
                    <a href="{{ route('jobs.search', [
                                    'q'      => $query,
                                    'type'   => $type,
                                    'source' => $filters['source'] ?? null,
                               ]) }}"
                       class="flex items-center px-3 py-2 rounded-xl text-sm no-underline
                              transition-colors duration-150 mt-0.5
                              {{ ($filters['type'] ?? '') === $type
                                  ? 'text-brand bg-brand/8'
                                  : 'text-text hover:bg-surface2' }}">
                        {{ $typeLabel }}
                    </a>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="bg-surface border border-border rounded-2xl p-4">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-muted mb-3">
                    Actions
                </p>
                <form action="{{ route('jobs.cache.clear') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 rounded-xl text-sm
                                   text-muted hover:bg-surface2 transition-colors
                                   duration-150 cursor-pointer bg-transparent border-none font-body">
                        🗑 Clear old cache
                    </button>
                </form>
            </div>

        </aside>

        {{-- ════════════════════════
             RESULTS
        ════════════════════════ --}}
        <div class="flex-1 min-w-0">

            {{-- Results header --}}
            <div class="flex items-baseline justify-between gap-4 mb-6 flex-wrap">
                <h1 class="font-display font-black text-2xl tracking-tight">
                    <span class="bg-gradient-to-br from-brand to-brand2 bg-clip-text text-transparent">
                        {{ count($jobs) }}
                    </span>
                    jobs found
                    @if(!empty($filters['source']))
                        <span class="text-base text-muted font-body font-normal">
                            via {{ $sourceMap[$filters['source']][0] ?? $filters['source'] }}
                        </span>
                    @endif
                </h1>
                <span class="text-xs text-muted">
                    for "{{ $query }}" · cached 1 hr
                </span>
            </div>

            {{-- ── Empty state ── --}}
            @if(count($jobs) === 0)
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-surface border border-border
                                flex items-center justify-center text-2xl mb-5">
                        🔍
                    </div>
                    <h2 class="font-display font-bold text-xl mb-2">No jobs found</h2>
                    <p class="text-muted text-sm max-w-xs leading-relaxed">
                        Try a different keyword or check back as our APIs refresh every hour.
                    </p>
                    <a href="{{ route('home') }}"
                       class="mt-6 px-5 py-2.5 rounded-xl border border-border text-sm text-muted
                              no-underline transition-all duration-150 hover:border-brand hover:text-brand">
                        ← New search
                    </a>
                </div>

            {{-- ── Job cards ── --}}
            @else
                <div class="flex flex-col gap-3">
                    @foreach($jobs as $job)
                        @php
                            $colorMap = [
                                'adzuna'       => 'var(--color-adzuna)',
                                'themuse'      => 'var(--color-themuse)',
                                'remotive'     => 'var(--color-remotive)',
                                'ziprecruiter' => 'var(--color-ziprecruiter)',
                            ];
                            $labelMap = [
                                'adzuna'       => 'Adzuna',
                                'themuse'      => 'The Muse',
                                'remotive'     => 'Remotive',
                                'ziprecruiter' => 'ZipRecruiter',
                            ];

                            $src     = $job['source'] ?? '';
                            $color   = $colorMap[$src] ?? 'var(--color-brand)';
                            $label   = $labelMap[$src] ?? ucfirst($src);
                            $initial = strtoupper(substr($job['company'] ?? '?', 0, 1));

                            $salary = '';
                            if (!empty($job['salary_min']) || !empty($job['salary_max'])) {
                                $sym    = ($job['salary_currency'] ?? 'USD') === 'GBP' ? '£' : '$';
                                $salary = $sym . number_format($job['salary_min'] ?? $job['salary_max']);
                                if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                    $salary .= ' – ' . $sym . number_format($job['salary_max']);
                                }
                            }
                        @endphp

                        <a href="{{ route('jobs.show', $job['id']) }}"
                           class="group relative flex gap-4 bg-surface border border-border
                                  rounded-2xl p-5 no-underline text-text overflow-hidden
                                  transition-all duration-200
                                  hover:border-brand/40 hover:-translate-y-0.5
                                  hover:shadow-[0_8px_32px_rgba(0,0,0,0.35)]">

                            {{-- Accent bar (left edge on hover) --}}
                            <span class="absolute left-0 top-0 bottom-0 w-[3px] rounded-l-2xl
                                         opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                  style="background:{{ $color }}">
                            </span>

                            {{-- Company logo / initial --}}
                            <div class="w-9 h-9 rounded-lg bg-surface2 border border-border shrink-0
                                        flex items-center justify-center font-display font-black
                                        text-sm overflow-hidden"
                                 style="color:{{ $color }}">
                                @if(!empty($job['logo_url']))
                                    <img src="{{ $job['logo_url'] }}"
                                         alt="{{ $job['company'] }}"
                                         class="w-full h-full object-contain p-1.5"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                    <span style="display:none">{{ $initial }}</span>
                                @else
                                    {{ $initial }}
                                @endif
                            </div>

                            {{-- Job info --}}
                            <div class="flex-1 min-w-0">

                                {{-- Title + arrow --}}
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <h2 class="font-display font-bold text-base leading-snug tracking-tight">
                                        {{ $job['title'] }}
                                    </h2>
                                    <svg class="text-muted w-4 h-4 shrink-0 mt-0.5
                                                group-hover:text-brand group-hover:translate-x-0.5
                                                transition-all duration-200"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>

                                {{-- Company · Location --}}
                                <p class="text-sm text-muted mb-2.5">
                                    {{ $job['company'] }}
                                    @if(!empty($job['location']))
                                        <span class="opacity-40 mx-1">·</span>
                                        {{ $job['location'] }}
                                    @endif
                                </p>

                                {{-- Description snippet --}}
                                @if(!empty($job['description']))
                                    <p class="text-[0.83rem] text-muted leading-relaxed mb-3 line-clamp-2">
                                        {{ $job['description'] }}
                                    </p>
                                @endif

                                {{-- Badges row --}}
                                <div class="flex items-center gap-2 flex-wrap">

                                    {{-- Source badge --}}
                                    <span class="text-[0.7rem] font-medium px-2.5 py-0.5 rounded-md border"
                                          style="color:{{ $color }};border-color:color-mix(in srgb,{{ $color }} 30%,transparent)">
                                        {{ $label }}
                                    </span>

                                    {{-- Job type --}}
                                    @if(!empty($job['job_type']))
                                        <span class="text-[0.7rem] text-muted px-2.5 py-0.5
                                                     rounded-md border border-border bg-surface2">
                                            {{ ucfirst(str_replace('_', ' ', $job['job_type'])) }}
                                        </span>
                                    @endif

                                    {{-- Category --}}
                                    @if(!empty($job['category']))
                                        <span class="text-[0.7rem] text-muted px-2.5 py-0.5
                                                     rounded-md border border-border bg-surface2">
                                            {{ $job['category'] }}
                                        </span>
                                    @endif

                                    {{-- Salary --}}
                                    @if($salary)
                                        <span class="text-[0.7rem] font-medium px-2.5 py-0.5
                                                     rounded-md border text-success
                                                     border-success/30 bg-success/5">
                                            {{ $salary }}
                                        </span>
                                    @endif

                                    {{-- Posted time --}}
                                    @if(!empty($job['posted_at']))
                                        <span class="text-[0.7rem] text-muted ml-auto">
                                            {{ \Carbon\Carbon::parse($job['posted_at'])->diffForHumans() }}
                                        </span>
                                    @endif

                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>

@endsection
