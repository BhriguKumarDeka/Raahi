<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-brand-neutral border border-transparent rounded-xl font-bold text-xs text-bg-primary uppercase tracking-wider hover:bg-brand-hover focus:bg-brand-hover active:bg-brand-neutral focus:outline-none focus:ring-2 focus:ring-brand-neutral focus:ring-offset-2 transition ease-in-out duration-150 shadow-none']) }}>
    {{ $slot }}
</button>
