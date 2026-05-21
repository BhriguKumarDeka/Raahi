<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2.5 bg-bg-secondary border border-border-card rounded-xl font-bold text-xs text-text-main uppercase tracking-wider transition hover:bg-bg-primary focus:outline-none focus:ring-2 focus:ring-brand-neutral focus:ring-offset-2 disabled:opacity-25 ease-in-out duration-150 shadow-none']) }}>
    {{ $slot }}
</button>
