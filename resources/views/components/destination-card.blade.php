@props([
    'title',
    'location',
    'image',
    'duration',
    'type',
    'dates',
    'accommodation',
    'transport',
    'meals',
    'price',
    'ctaUrl' => '#',
])

<div class="bg-bg-primary rounded-3xl border border-border-card shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between overflow-hidden group">
    <div class="p-4 flex-1">
        <!-- Card Header -->
        <div class="flex justify-between items-center mb-3">
            <div>
                <h4 class="font-extrabold text-sm text-text-main">{{ $title }}</h4>
                <p class="text-[10px] text-text-muted">{{ $location }}</p>
            </div>
        </div>

        <!-- Image with hover scaling -->
        <div class="relative h-[200px] rounded-2xl overflow-hidden bg-bg-secondary border border-border-light">
            <img src="{{ $image }}" 
                 alt="{{ $title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
            <!-- Overlay stats (standardized to Phosphor icons, no emojis) -->
            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[9px] font-bold bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                <span class="flex items-center gap-1">
                    <i class="ph ph-clock text-xs"></i> 
                    <span>{{ $duration }}</span>
                </span>
                <span class="flex items-center gap-1">
                    <i class="ph ph-globe text-xs"></i> 
                    <span>{{ $type }}</span>
                </span>
                <span class="flex items-center gap-1">
                    <i class="ph ph-calendar text-xs"></i> 
                    <span>{{ $dates }}</span>
                </span>
            </div>
        </div>

        <!-- Package Details -->
        <div class="mt-4 pt-4 border-t border-border-light space-y-2 text-[10px] text-text-muted">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-text-main">Accommodation</span>
                <span>{{ $accommodation }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="font-semibold text-text-main">Transport</span>
                <span>{{ $transport }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="font-semibold text-text-main">Meals</span>
                <span>{{ $meals }}</span>
            </div>
        </div>
    </div>

    <!-- Bottom Price & Action Footer -->
    <div class="px-6 py-4 bg-bg-secondary border-t border-border-light flex justify-between items-center">
        <div>
            <p class="text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Starting at</p>
            <p class="font-extrabold text-base text-text-main">{{ $price }} <span class="text-[9px] font-normal text-text-muted">/ person</span></p>
        </div>
        <a href="{{ $ctaUrl }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm cursor-pointer">
            <i class="ph ph-arrow-right text-sm"></i>
        </a>
    </div>
</div>
