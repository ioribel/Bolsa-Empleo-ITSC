@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        previouslyFocused: null,
        focusables() {
            let selector = 'a, button, input:not([type=hidden]), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                .filter(el => !el.hasAttribute('disabled') && el.getClientRects().length > 0)
        },
        trapFocus(event) {
            const elements = this.focusables()
            const first = elements[0]
            const last = elements[elements.length - 1]

            if (!elements.length) {
                event.preventDefault()
                this.$refs.dialog.focus()
            } else if (event.shiftKey && (document.activeElement === first || !this.$el.contains(document.activeElement))) {
                event.preventDefault()
                last.focus()
            } else if (!event.shiftKey && (document.activeElement === last || !this.$el.contains(document.activeElement))) {
                event.preventDefault()
                first.focus()
            }
        },
    }"
    x-init="
        const open = () => {
            previouslyFocused = document.activeElement;
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => (focusables()[0] || $refs.dialog).focus(), 100)' : '' }}
        };
        const close = () => {
            document.body.classList.remove('overflow-y-hidden');
            if (previouslyFocused && previouslyFocused.isConnected) previouslyFocused.focus();
        };
        if (show) open();
        $watch('show', value => value ? open() : close());
    "
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="if (show) show = false"
    x-on:keydown.tab="trapFocus($event)"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition motion-reduce:transition-none"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        x-ref="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $name }}-title"
        tabindex="-1"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition motion-reduce:transition-none sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
