<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-rose-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider hover:bg-rose-750 active:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-none']) }}>
    {{ $slot }}
</button>
