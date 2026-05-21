@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-neutral text-start text-base font-semibold text-brand-neutral bg-bg-secondary focus:outline-none transition duration-150 ease-in-out cursor-pointer'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-text-muted hover:text-text-main hover:bg-bg-secondary hover:border-border-card focus:outline-none transition duration-150 ease-in-out cursor-pointer';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
