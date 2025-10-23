@props(['active'])

@php
$classes = $active ?? false
? 'flex items-center px-4 py-2 text-gray-100 bg-gray-700 rounded-lg'
: 'flex items-center px-4 py-2 text-gray-300 hover:text-gray-100 hover:bg-gray-700 rounded-lg transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>