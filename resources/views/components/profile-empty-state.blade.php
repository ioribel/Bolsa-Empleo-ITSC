@props(['text'])

<div {{ $attributes->class('rounded-xl border-2 border-dashed border-itsc-slate-300 p-4 text-center') }}>
    @isset($icon)
        <span class="mx-auto mb-2 flex h-9 w-9 items-center justify-center text-itsc-slate-500">{{ $icon }}</span>
    @endisset
    <p class="text-sm text-itsc-slate-500">{{ $text }}</p>
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
