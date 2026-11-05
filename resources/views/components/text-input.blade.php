@props(['disabled' => false])

@php
    $inputName = $attributes->get('name');
    $hasError = $inputName && $errors->has($inputName);
@endphp

<input @disabled($disabled) @if ($hasError) aria-invalid="true" @endif {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
