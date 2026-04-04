<div>

    {{-- ───────────────────────────────────────────────
         HERO / SEARCH SECTION  (shown when no results)
    ─────────────────────────────────────────────────── --}}
    @if(!$searched)
    <section wire:loading.remove wire:target="quickSearch"
             class="min-h-[calc(100vh-60px)] flex flex-col items-center justify-center
                    px-6 py-20 relative overflow-hidden">

        {{-- Ambient glows --}}
        <div class="absolute w-[600px] h-[600px] rounded-full pointer-events-none
                    bg-radial-[at_50%_50%] from-brand/10 to-transparent
                    top-1/2 left-1/2 -translate-x-1/2 -translate-y-[60%]"></div>
        <div class="absolute w-[350px] h-[350px] rounded-full pointer-events-none
                    bg-radial-[at_50%_50%] from-brand2/7 to-transparent
                    bottom-[10%] right-[8%]"></div>

        {{-- Eyebrow --}}
        <div class="animate-fade-up delay-0 flex items-center gap-3 mb-6">
            <span class="h-px w-9 bg-brand opacity-40"></span>
            <span class="text-[0.7rem] font-semibold tracking-[0.15em] uppercase text-brand">
                Unified Job Intelligence
            </span>
            <span class="h-px w-9 bg-brand opacity-40"></span>
        </div>

        {{-- Headline --}}
        <h1 class="animate-fade-up delay-100 font-display font-black text-center
                   leading-[1.05] tracking-tight mb-5
                   text-5xl md:text-6xl lg:text-7xl max-w-3xl">
            One search.<br>
            <span class="text-brand">
                Every opportunity.
            </span>
        </h1>

        {{-- Subtitle --}}
        <p class="animate-fade-up delay-200 text-muted text-center
                  text-base md:text-lg font-light max-w-md mb-11 leading-relaxed">
            Search across Adzuna, The Muse, Remotive, and JSearch simultaneously, all results in one clean view.
        </p>

        {{-- Search form --}}
        <div class="animate-fade-up delay-300 w-full max-w-[660px]">
            <form wire:submit="search">
                <div class="flex items-center gap-3 bg-surface border border-border
                            rounded-2xl px-5 py-2 transition-all duration-200
                            focus-within:border-brand focus-within:shadow-[0_0_0_3px_rgba(91,127,255,0.12)]">
                    <svg class="text-muted shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text"
                           wire:model="query"
                           class="flex-1 bg-transparent border-none outline-none text-text
                                  placeholder:text-muted text-base py-2.5 font-body"
                           placeholder="e.g. Laravel Developer, Data Analyst, Designer..."
                           autocomplete="off"
                           autofocus>
                    <button type="submit"
                            class="shrink-0 bg-brand
                                   text-white font-display font-bold text-sm
                                   px-6 py-3 rounded-xl tracking-wide cursor-pointer
                                   transition-all duration-150
                                   hover:opacity-90 hover:-translate-y-px active:translate-y-0">
                        <span wire:loading.remove wire:target="search">Search Jobs</span>
                        <span wire:loading wire:target="search">Searching…</span>
                    </button>
                </div>
            </form>

            {{-- Trending tags --}}
            <div class="mt-5 flex items-center gap-2.5 flex-wrap justify-center">
                <span class="text-[0.7rem] text-muted uppercase tracking-widest">Try:</span>
                @foreach(['Laravel', 'Python', 'Product Manager', 'UX Designer', 'DevOps', 'Data Scientist'] as $tag)
                    <button wire:click="quickSearch('{{ $tag }}')"
                            type="button"
                            class="text-[0.78rem] px-3.5 py-1 rounded-full border border-border text-muted
                                   transition-all duration-200 cursor-pointer bg-transparent font-body
                                   hover:border-brand hover:text-brand hover:bg-brand/5">
                        {{ $tag }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Source badges --}}
        <div class="animate-fade-up delay-400 flex flex-col items-center gap-4 mt-20">
            <span class="text-[0.68rem] text-muted uppercase tracking-[0.12em]">Powered by</span>
            <div class="flex gap-2.5 flex-wrap justify-center">
                @foreach([
                    ['Adzuna',       'var(--color-adzuna)'],
                    ['The Muse',     'var(--color-themuse)'],
                    ['Remotive',     'var(--color-remotive)'],
                    ['JSearch', 'var(--color-jsearch)'],
                ] as [$name, $color])
                    <span class="flex items-center gap-2 px-4 py-2 rounded-full
                                 border border-border bg-surface text-text text-[0.8rem] font-medium">
                        <span class="w-2 h-2 rounded-full" style="background:{{ $color }}"></span>
                        {{ $name }}
                    </span>
                @endforeach
            </div>
        </div>

    </section>

    {{-- Loading skeleton shown while quickSearch is pending (hero → results) --}}
    <section wire:loading wire:target="quickSearch"
             class="min-h-[calc(100vh-60px)] flex flex-col justify-center px-6 py-12">
        <div class="max-w-2xl w-full mx-auto flex flex-col gap-3">
            @for($i = 0; $i < 7; $i++)
                <div class="flex gap-4 bg-surface border border-border rounded-2xl p-5">
                    <div class="skeleton w-9 h-9 rounded-lg shrink-0"></div>
                    <div class="flex-1 min-w-0 flex flex-col gap-2.5">
                        <div class="skeleton h-4 w-3/5 rounded"></div>
                        <div class="skeleton h-3 w-2/5 rounded"></div>
                        <div class="skeleton h-3 w-full rounded"></div>
                        <div class="skeleton h-3 w-4/5 rounded"></div>
                        <div class="flex gap-2 mt-1">
                            <div class="skeleton h-5 w-16 rounded-md"></div>
                            <div class="skeleton h-5 w-14 rounded-md"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </section>
    @endif

    {{-- ───────────────────────────────────────────────
         RESULTS VIEW
    ─────────────────────────────────────────────────── --}}
    @if($searched)

    {{-- Sticky search bar --}}
    <div class="sticky top-15 z-40 border-b border-border bg-bg/90 backdrop-blur-lg py-3">
        <div class="max-w-6xl mx-auto px-6">
            <form wire:submit="search">
                <div class="flex items-center gap-3 bg-surface border border-border
                            rounded-2xl px-5 py-2 max-w-2xl transition-all duration-200
                            focus-within:border-brand focus-within:shadow-[0_0_0_3px_rgba(91,127,255,0.12)]">
                    <svg class="text-muted shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text"
                           wire:model="query"
                           class="flex-1 bg-transparent border-none outline-none text-text
                                  placeholder:text-muted text-base py-2.5 font-body"
                           placeholder="Search jobs..."
                           autocomplete="off">
                    <button type="submit"
                            class="shrink-0 bg-brand
                                   text-white font-display font-bold text-sm
                                   px-6 py-3 rounded-xl tracking-wide cursor-pointer
                                   transition-all duration-150
                                   hover:opacity-90 hover:-translate-y-px active:translate-y-0">
                        <span wire:loading.remove wire:target="search">Search</span>
                        <span wire:loading wire:target="search">…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Main layout --}}
    <div class="max-w-6xl mx-auto px-6 py-8">
        <div class="flex gap-7 items-start">

            {{-- ════ SIDEBAR ════ --}}
            <aside class="w-56 shrink-0 sticky top-[120px] hidden md:flex flex-col gap-3">

                {{-- Flash success --}}
                @if(session('success'))
                    <div class="px-4 py-3 rounded-xl border border-success text-success text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Source filter --}}
                <div class="bg-surface border border-border rounded-2xl p-4">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-muted mb-3">
                        Source
                    </p>

                    <button wire:click="clearFilters" type="button"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm
                                   transition-colors duration-150 cursor-pointer bg-transparent border-none font-body
                                   {{ $source === '' ? 'text-brand bg-brand/8' : 'text-text hover:bg-surface2' }}">
                        <span>All Sources</span>
                        <span class="text-[0.7rem] text-muted bg-surface2 px-2 py-0.5 rounded-full">
                            {{ array_sum($sourceCounts) }}
                        </span>
                    </button>

                    @php
                        $sourceMap = [
                            'adzuna'       => ['Adzuna',       'var(--color-adzuna)'],
                            'themuse'      => ['The Muse',     'var(--color-themuse)'],
                            'remotive'     => ['Remotive',     'var(--color-remotive)'],
                            'jsearch' => ['JSearch', 'var(--color-jsearch)'],
                        ];
                    @endphp

                    @foreach($sourceMap as $key => [$srcLabel, $color])
                        @php $count = $sourceCounts[$key] ?? 0; @endphp
                        @if($count > 0)
                            <button wire:click="filterSource('{{ $key }}')" type="button"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm
                                           transition-colors duration-150 mt-0.5 cursor-pointer bg-transparent
                                           border-none font-body
                                           {{ $source === $key ? 'bg-surface2' : 'text-text hover:bg-surface2' }}"
                                    style="{{ $source === $key ? "color:{$color}" : '' }}">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $color }}"></span>
                                    {{ $srcLabel }}
                                </span>
                                <span class="text-[0.7rem] text-muted bg-surface2 px-2 py-0.5 rounded-full">
                                    {{ $count }}
                                </span>
                            </button>
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
                    ] as $typeKey => $typeLabel)
                        <button wire:click="filterType('{{ $typeKey }}')" type="button"
                                class="w-full flex items-center px-3 py-2 rounded-xl text-sm
                                       transition-colors duration-150 mt-0.5 cursor-pointer
                                       bg-transparent border-none font-body
                                       {{ $type === $typeKey ? 'text-brand bg-brand/8' : 'text-text hover:bg-surface2' }}">
                            {{ $typeLabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="bg-surface border border-border rounded-2xl p-4">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-muted mb-3">
                        Actions
                    </p>
                    <button wire:click="clearCache" type="button"
                            class="w-full text-left px-3 py-2 rounded-xl text-sm text-muted
                                   hover:bg-surface2 transition-colors duration-150
                                   cursor-pointer bg-transparent border-none font-body">
                        🗑 Clear old cache
                    </button>
                </div>

            </aside>

            {{-- ════ RESULTS ════ --}}
            <div class="flex-1 min-w-0">

                {{-- Skeleton loading cards --}}
                <div wire:loading wire:target="search,filterSource,filterType,clearFilters,quickSearch"
                     class="flex flex-col gap-3">
                    @for($i = 0; $i < 5; $i++)
                        <div class="flex gap-4 bg-surface border border-border rounded-2xl p-5">
                            <div class="skeleton w-9 h-9 rounded-lg shrink-0"></div>
                            <div class="flex-1 min-w-0 flex flex-col gap-2.5">
                                <div class="skeleton h-4 w-3/5 rounded"></div>
                                <div class="skeleton h-3 w-2/5 rounded"></div>
                                <div class="skeleton h-3 w-full rounded"></div>
                                <div class="skeleton h-3 w-4/5 rounded"></div>
                                <div class="flex gap-2 mt-1">
                                    <div class="skeleton h-5 w-16 rounded-md"></div>
                                    <div class="skeleton h-5 w-14 rounded-md"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Results header --}}
                <div wire:loading.remove
                     class="flex items-baseline justify-between gap-4 mb-6 flex-wrap">
                    <h1 class="font-display font-black text-2xl tracking-tight">
                        <span class="text-brand">
                            {{ count($jobs) }}
                        </span>
                        jobs found
                        @if($source !== '')
                            <span class="text-base text-muted font-body font-normal">
                                via {{ $sourceMap[$source][0] ?? $source }}
                            </span>
                        @endif
                    </h1>
                    <span class="text-xs text-muted">
                        for "{{ $query }}" · cached 1 hr
                    </span>
                </div>

                {{-- Empty state --}}
                @if(count($jobs) === 0)
                    <div wire:loading.remove
                         class="flex flex-col items-center justify-center py-24 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-surface border border-border
                                    flex items-center justify-center text-2xl mb-5">🔍</div>
                        <h2 class="font-display font-bold text-xl mb-2">No jobs found</h2>
                        <p class="text-muted text-sm max-w-xs leading-relaxed">
                            Try a different keyword or check back as our APIs refresh every hour.
                        </p>
                        <button wire:click="$set('searched', false)" type="button"
                                class="mt-6 px-5 py-2.5 rounded-xl border border-border text-sm text-muted
                                       no-underline transition-all duration-150 cursor-pointer bg-transparent
                                       font-body hover:border-brand hover:text-brand">
                            ← New search
                        </button>
                    </div>

                {{-- Job cards --}}
                @else
                    <div wire:loading.remove class="flex flex-col gap-3">
                        @foreach($jobs as $job)
                            @php
                                $colorMap = [
                                    'adzuna'       => 'var(--color-adzuna)',
                                    'themuse'      => 'var(--color-themuse)',
                                    'remotive'     => 'var(--color-remotive)',
                                    'jsearch' => 'var(--color-jsearch)',
                                ];
                                $labelMap = [
                                    'adzuna'       => 'Adzuna',
                                    'themuse'      => 'The Muse',
                                    'remotive'     => 'Remotive',
                                    'jsearch' => 'JSearch',
                                ];
                                $src     = $job['source'] ?? '';
                                $color   = $colorMap[$src] ?? 'var(--color-brand)';
                                $label   = $labelMap[$src] ?? ucfirst($src);
                                $initial = strtoupper(substr($job['company'] ?? '?', 0, 1));

                                $salary = '';
                                if (!empty($job['salary_min']) || !empty($job['salary_max'])) {
                                    $sym = ($job['salary_currency'] ?? 'USD') === 'GBP' ? '£' : '$';
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

                                {{-- Accent bar --}}
                                <span class="absolute left-0 top-0 bottom-0 w-[3px] rounded-l-2xl
                                             opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                      style="background:{{ $color }}"></span>

                                {{-- Company avatar --}}
                                <div class="w-9 h-9 rounded-lg bg-surface2 border border-border shrink-0
                                            flex items-center justify-center font-display font-black
                                            text-sm overflow-hidden"
                                     style="color:{{ $color }}">
                                    @if(!empty($job['logo_url']))
                                        <img src="{{ $job['logo_url'] }}"
                                             alt="{{ $job['company'] }}"
                                             class="w-full h-full object-contain p-1"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <span style="display:none">{{ $initial }}</span>
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>

                                {{-- Job info --}}
                                <div class="flex-1 min-w-0">
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

                                    <p class="text-sm text-muted mb-2.5">
                                        {{ $job['company'] }}
                                        @if(!empty($job['location']))
                                            <span class="opacity-40 mx-1">·</span>
                                            {{ $job['location'] }}
                                        @endif
                                    </p>

                                    @if(!empty($job['description']))
                                        <p class="text-[0.83rem] text-muted leading-relaxed mb-3 line-clamp-2">
                                            {{ $job['description'] }}
                                        </p>
                                    @endif

                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[0.7rem] font-medium px-2.5 py-0.5 rounded-md border"
                                              style="color:{{ $color }};border-color:color-mix(in srgb,{{ $color }} 30%,transparent)">
                                            {{ $label }}
                                        </span>

                                        @if(!empty($job['job_type']))
                                            <span class="text-[0.7rem] text-muted px-2.5 py-0.5
                                                         rounded-md border border-border bg-surface2">
                                                {{ ucfirst(str_replace('_', ' ', $job['job_type'])) }}
                                            </span>
                                        @endif

                                        @if(!empty($job['category']))
                                            <span class="text-[0.7rem] text-muted px-2.5 py-0.5
                                                         rounded-md border border-border bg-surface2">
                                                {{ $job['category'] }}
                                            </span>
                                        @endif

                                        @if($salary)
                                            <span class="text-[0.7rem] font-medium px-2.5 py-0.5
                                                         rounded-md border text-success border-success/30 bg-success/5">
                                                {{ $salary }}
                                            </span>
                                        @endif

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

    @endif

</div>
