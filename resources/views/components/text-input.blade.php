@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-border-card bg-bg-primary text-text-main focus:border-brand-neutral focus:ring-1 focus:ring-brand-neutral rounded-xl px-4 py-2.5 transition duration-150 ease-in-out text-sm focus:outline-none']) }}>
