@props(['active' => false])

@php
$classes = $active
    ? 'block border-l-4 border-indigo-400 bg-indigo-50 py-2 ps-3 pe-4 text-start text-base font-medium text-indigo-700'
    : 'block border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
