@props(['percentage'])

@php
    $percentage = max(0, min(100, (int) $percentage));
    $circumference = 2 * pi() * 15.5;
    $offset = $circumference * (1 - $percentage / 100);
@endphp

<svg class="h-16 w-16 shrink-0 -rotate-90" viewBox="0 0 40 40" role="img"
    aria-label="Completitud del perfil: {{ $percentage }} por ciento">
    <circle cx="20" cy="20" r="15.5" fill="none" stroke="currentColor" stroke-width="3" class="text-itsc-slate-100" />
    <circle cx="20" cy="20" r="15.5" fill="none" stroke="currentColor" stroke-width="3"
        stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
        class="text-itsc-red-600 transition-[stroke-dashoffset] motion-reduce:transition-none" />
    <text x="20" y="20" text-anchor="middle" dominant-baseline="central"
        class="rotate-90 origin-center fill-itsc-slate-900 text-[7px] font-medium">{{ $percentage }}%</text>
</svg>
