<section {{ $attributes->class('rounded-xl border border-itsc-slate-100 bg-white p-4 sm:p-5') }}>
    @if (isset($header) || isset($actions))
        <header class="mb-4 flex items-center justify-between gap-3">
            <div class="min-w-0">{{ $header ?? '' }}</div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    {{ $slot }}
</section>
