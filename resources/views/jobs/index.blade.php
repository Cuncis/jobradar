@extends('layouts.app')

@section('title', 'JobRadar — Search Jobs Across All Platforms')

@section('content')
    <section class="min-h-[calc(100vh-60px)] flex flex-col items-center justify-center
                    px-6 py-20 relative overflow-hidden">

        {{-- Ambient background glows --}}
        <div class="absolute w-[600px] h-[600px] rounded-full pointer-events-none
                    bg-radial-[at_50%_50%] from-brand/10 to-transparent
                    top-1/2 left-1/2 -translate-x-1/2 -translate-y-[60%]">
        </div>
        <div class="absolute w-[350px] h-[350px] rounded-full pointer-events-none
                    bg-radial-[at_50%_50%] from-brand2/7 to-transparent
                    bottom-[10%] right-[8%]">
        </div>

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
            <span class="bg-gradient-to-br from-brand to-brand2
                         bg-clip-text text-transparent">
                Every opportunity.
            </span>
        </h1>

        {{-- Subtitle --}}
        <p class="animate-fade-up delay-200 text-muted text-center
                  text-base md:text-lg font-light max-w-md mb-11 leading-relaxed">
            Search across Adzuna, The Muse, Remotive, and ZipRecruiter simultaneously —
            all results in one clean view.
        </p>

        {{-- Search form --}}
        <div class="animate-fade-up delay-300 w-full max-w-[660px]">
            <form action="{{ route('jobs.search') }}" method="GET">
                <div class="flex items-center gap-3 bg-surface border border-border
                            rounded-2xl px-5 py-2 transition-all duration-200
                            focus-within:border-brand focus-within:shadow-[0_0_0_3px_rgba(91,127,255,0.12)]">

                    {{-- Search icon --}}
                    <svg class="text-muted shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>

                    <input type="text" name="q" class="flex-1 bg-transparent border-none outline-none text-text
                               placeholder:text-muted text-base py-2.5 font-body"
                        placeholder="e.g. Laravel Developer, Data Analyst, Designer..." autocomplete="off" autofocus>

                    <button type="submit" class="shrink-0 bg-gradient-to-br from-brand to-brand2
                                   text-white font-display font-bold text-sm
                                   px-6 py-3 rounded-xl tracking-wide
                                   transition-all duration-150 cursor-pointer
                                   hover:opacity-90 hover:-translate-y-px
                                   active:translate-y-0">
                        Search Jobs
                    </button>
                </div>
            </form>

            {{-- Trending tags --}}
            <div class="mt-5 flex items-center gap-2.5 flex-wrap justify-center">
                <span class="text-[0.7rem] text-muted uppercase tracking-widest">Try:</span>
                @foreach(['Laravel', 'Python', 'Product Manager', 'UX Designer', 'DevOps', 'Data Scientist'] as $tag)
                    <a href="{{ route('jobs.search', ['q' => $tag]) }}" class="text-[0.78rem] px-3.5 py-1 rounded-full
                                  border border-border text-muted no-underline
                                  transition-all duration-200
                                  hover:border-brand hover:text-brand hover:bg-brand/5">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Source badges --}}
        <div class="animate-fade-up delay-400 flex flex-col items-center gap-4 mt-20">
            <span class="text-[0.68rem] text-muted uppercase tracking-[0.12em]">Powered by</span>
            <div class="flex gap-2.5 flex-wrap justify-center">

                @foreach([
                        ['Adzuna', 'var(--color-adzuna)'],
                        ['The Muse', 'var(--color-themuse)'],
                        ['Remotive', 'var(--color-remotive)'],
                        ['ZipRecruiter', 'var(--color-ziprecruiter)'],
                    ] as [$name, $color])
                        <span class="flex items-center gap-2 px-4 py-2 rounded-full
                                     border border-border bg-surface text-text text-[0.8rem] font-medium">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $color }}"></span>
                            {{ $name }}
                        </span>
                @endforeach

            </div>
        </div>

    </section>
@endsection