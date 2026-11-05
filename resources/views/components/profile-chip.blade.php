@props(['level'])

@php
    $normalizedLevel = mb_strtolower($level);
    $chipClasses = match ($normalizedLevel) {
        'avanzado' => 'bg-itsc-red-50 text-itsc-red-700',
        'intermedio' => 'bg-itsc-slate-100 text-itsc-slate-700',
        default => 'bg-itsc-slate-50 text-itsc-slate-500',
    };
@endphp

<span {{ $attributes->class("inline-flex max-w-full items-center gap-1.5 rounded-full py-1 pl-3 pr-1.5 text-[13px] {$chipClasses}") }}>
    {{ $slot }}
    <span class="rounded-full bg-white px-2 text-[11px] text-itsc-slate-500">{{ ucfirst($level) }}</span>
</span>
