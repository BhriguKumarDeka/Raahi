@props(['user', 'class' => 'h-10 w-10 text-sm'])

<div {{ $attributes->merge(['class' => 'rounded-full overflow-hidden flex-shrink-0 bg-brand-neutral/10 flex flex-col justify-center items-center text-brand-neutral font-bold uppercase ' . $class]) }}>
  @if(isset($user->profile_image) && $user->profile_image)
  <img src="{{ Storage::url($user->profile_image) }}" alt="{{ $user->name }}" class="h-full w-full object-cover shrink-0">
  @else
  <span>{{ substr($user->name ?? '?', 0, 1) }}</span>
  @endif
</div>