@props(['active' => false])

@php
$classes = $active
            ? 'flex cursor-pointer items-center p-2 text-[#0083E9] rounded-md bg-gray-100 group text-sm'
            : 'flex cursor-pointer items-center p-2 text-gray-600 rounded-md hover:bg-gray-100 group text-sm';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
