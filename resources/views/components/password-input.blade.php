@props(['containerClass' => ''])

<div x-data="{ visible: false }" class="relative {{ $containerClass }}">
    <x-text-input
        {{ $attributes->merge(['class' => 'pr-12']) }}
        type="password"
        x-bind:type="visible ? 'text' : 'password'"
    />

    <button
        type="button"
        x-cloak
        x-on:click="visible = !visible"
        x-bind:aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
        x-bind:aria-pressed="visible"
        x-bind:title="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
        class="absolute inset-y-0 right-0 inline-flex items-center justify-center rounded-r-md px-3 text-gray-500 transition-colors hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#A32D2D] dark:text-gray-400 dark:hover:text-gray-200"
    >
        <svg x-show="!visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <svg x-cloak x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58a2 2 0 0 0 2.83 2.83m3.7 3.7A10.6 10.6 0 0 1 12 18.75c-6.25 0-9.75-6.75-9.75-6.75a17.2 17.2 0 0 1 4.25-4.9m3.23-1.74A10.6 10.6 0 0 1 12 5.25c6.25 0 9.75 6.75 9.75 6.75a17.4 17.4 0 0 1-3.14 4.14" />
        </svg>
    </button>
</div>
