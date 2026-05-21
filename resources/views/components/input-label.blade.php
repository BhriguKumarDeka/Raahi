@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-xs uppercase tracking-wider text-text-muted']) }}>
    {{ $value ?? $slot }}
</label>
