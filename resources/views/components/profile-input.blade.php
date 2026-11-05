@props([
    'label',
    'id',
    'error' => null,
    'readonly' => false,
    'value' => null,
    'as' => 'input',
    'rows' => 3,
])

<div>
    @if ($readonly)
        <p class="mb-1 text-xs text-itsc-slate-500">{{ $label }}</p>
        <div id="{{ $id }}" role="group" aria-label="{{ $label }}" class="flex min-h-10 items-center gap-2 rounded-lg bg-itsc-slate-50 px-2.5 py-2 text-[13px] text-itsc-slate-700">
            @isset($icon)
                <span class="shrink-0 text-itsc-slate-500">{{ $icon }}</span>
            @endisset
            <span class="min-w-0 break-all">{{ $value }}</span>
        </div>
    @else
        <label for="{{ $id }}" class="mb-1 block text-xs text-itsc-slate-500">{{ $label }}</label>
        @if ($as === 'textarea')
            <textarea id="{{ $id }}" rows="{{ $rows }}" @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                {{ $attributes->merge(['class' => 'block w-full resize-y rounded-lg border border-itsc-slate-300 bg-white px-3 py-2 text-sm text-itsc-slate-900 placeholder:text-itsc-slate-500 focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15']) }}>{{ $value }}</textarea>
        @else
            <input id="{{ $id }}" value="{{ $value }}" @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                {{ $attributes->merge(['class' => 'block h-10 w-full rounded-lg border border-itsc-slate-300 bg-white px-3 text-sm text-itsc-slate-900 placeholder:text-itsc-slate-500 focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15']) }}>
        @endif
    @endif

    @if ($error)
        <p id="{{ $id }}-error" role="alert" class="mt-1 flex items-start gap-1 text-xs text-red-800">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
            </svg>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
