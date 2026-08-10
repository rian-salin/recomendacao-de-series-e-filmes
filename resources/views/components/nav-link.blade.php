@props(['active' => false])

@php
$classes = $active
    ? 'inline-flex items-center border-b-2 border-indigo-400 px-1 pt-1 text-sm font-medium text-gray-900'
    : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
