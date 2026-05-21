<?php

use function Livewire\Volt\{state, computed, on, mount};
use App\Models\Trip;

state([
    'trip',
    'activeTab' => 'itinerary',
]);

on(['trip-updated' => '$refresh']);

mount(function (Trip $trip) {
    $this->trip = $trip;
    if (empty($trip->cover_image_url)) {
        $data = \App\Services\PexelsService::getTripImageData($trip->destination);
        $trip->update([
            'cover_image_url' => $data['url'],
            'photographer_name' => $data['photographer'],
            'photographer_url' => $data['photographer_url'],
            'photo_url' => $data['photo_url'],
        ]);
        $this->trip = $trip->fresh();
    }
});

$users = computed(function () {
    return $this->trip->users;
});

?>
<div class="py-8 bg-bg-secondary min-h-screen font-sans text-text-main"
     x-data="{}"
     x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1] }, { duration: 0.4 }) }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Trip Header Atmospheric Banner -->
        @php
            $headerImage = $trip->cover_image_url ?: \App\Services\PexelsService::getTripImage($trip->destination);
        @endphp
        <div class="relative border border-border-light rounded-3xl p-6 md:p-8 mb-8 shadow-sm overflow-hidden bg-brand-neutral bg-cover bg-center"
             style="background-image: linear-gradient(to bottom, rgba(26, 59, 43, 0.4), rgba(26, 59, 43, 0.85)), url('{{ $headerImage }}');"
             x-init="if(window.Motion) { window.Motion.animate($el, { y: [15, 0], opacity: [0, 1] }, { duration: 0.5, delay: 0.1 }) }">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                <div>
                    <span class="text-xs text-white font-extrabold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">{{ $trip->destination }}</span>
                    <h1 class="text-3.5xl font-extrabold tracking-tight mt-3 font-serif-display text-white">{{ $trip->name }}</h1>
                    <p class="text-white/85 text-sm mt-2 leading-relaxed max-w-2xl">{{ $trip->description ?: 'Explore, plan, and budget this journey together.' }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/25 px-5 py-4 rounded-2xl text-center min-w-[160px] shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                    <span class="text-[10px] font-bold text-white/70 uppercase tracking-wider block">Time to Departure</span>
                    @php
                        $daysLeft = now()->startOfDay()->diffInDays($trip->start_date, false);
                    @endphp
                    <span class="text-2xl font-extrabold tracking-tight text-white block mt-1">
                        @if ($daysLeft > 0)
                            {{ $daysLeft }} {{ Str::plural('Day', $daysLeft) }}
                        @elseif ($daysLeft === 0)
                            Today!
                        @else
                            Completed
                        @endif
                    </span>
                </div>
            </div>
            
            <!-- Dates and details bar -->
            <div class="flex flex-wrap items-center mt-6 pt-6 border-t border-white/20 text-xs font-semibold text-white/80 gap-6 relative z-10">
                <div class="flex items-center space-x-2">
                    <i class="ph-bold ph-calendar-blank text-base text-white"></i>
                    <span>{{ $trip->start_date->format('M d, Y') }} - {{ $trip->end_date->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="ph-bold ph-wallet text-base text-white"></i>
                    <span>Budget limit: <strong class="text-white font-bold">₹{{ number_format($trip->budget_estimate, 0) }}</strong></span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="ph-bold ph-users-three text-base text-white"></i>
                    <span>{{ $this->users->count() }} travelers</span>
                </div>
                @if ($trip->photographer_name)
                    <div class="flex items-center space-x-2 bg-white/10 px-2 py-0.5 rounded backdrop-blur-sm">
                        <i class="ph-bold ph-camera text-base text-white"></i>
                        <span>
                            Photo by <a href="{{ $trip->photographer_url }}" target="_blank" class="underline hover:text-white font-bold">{{ $trip->photographer_name }}</a> on 
                            <a href="{{ str_contains($trip->photographer_url, 'pexels') ? 'https://www.pexels.com' : 'https://unsplash.com' }}" target="_blank" class="underline hover:text-white font-bold">
                                {{ str_contains($trip->photographer_url, 'pexels') ? 'Pexels' : 'Unsplash' }}
                            </a>
                        </span>
                    </div>
                @endif
                <div class="flex -space-x-1.5 ml-auto">
                    @foreach($this->users->take(4) as $u)
                        <div class="h-6 w-6 rounded-full bg-white text-brand-neutral border-2 border-brand-neutral/20 flex items-center justify-center font-bold text-[9px] uppercase"
                             title="{{ $u->name }} ({{ $trip->getUserRole($u) }})">
                            {{ $u->name[0] }}
                        </div>
                    @endforeach
                    @if($this->users->count() > 4)
                        <div class="h-6 w-6 rounded-full bg-white/20 text-white border-2 border-brand-neutral/20 flex items-center justify-center font-bold text-[9px] backdrop-blur-sm">
                            +{{ $this->users->count() - 4 }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- LEFT PANEL: Primary Workspaces (Timeline, Ledger, Files, Travelers) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Workspace Tabs -->
                <div class="border-b border-border-light bg-bg-primary rounded-2xl p-1.5 border flex space-x-1 overflow-x-auto">
                    @foreach ([
                        'itinerary' => ['label' => 'Journey Timeline', 'icon' => 'ph-map-trifold'],
                        'budget' => ['label' => 'Shared Ledger', 'icon' => 'ph-receipt'],
                        'documents' => ['label' => 'Documents & Tickets', 'icon' => 'ph-file-text'],
                        'members' => ['label' => 'Travelers', 'icon' => 'ph-users-three']
                    ] as $tab => $info)
                        <button type="button" 
                                wire:click="$set('activeTab', '{{ $tab }}')"
                                class="flex items-center space-x-2 px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap focus:outline-none transition duration-150 cursor-pointer {{ $activeTab === $tab ? 'bg-brand-neutral text-bg-primary shadow-sm' : 'text-text-muted hover:text-text-main hover:bg-bg-secondary' }}">
                            <i class="ph-bold {{ $info['icon'] }} text-sm"></i>
                            <span>{{ $info['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Tab Views Container -->
                <div class="transition-all duration-350">
                    
                    <!-- JOURNEY TIMELINE TAB -->
                    @if ($activeTab === 'itinerary')
                        <livewire:trip.itinerary-section :trip="$trip" />
                    @endif

                    <!-- SHARED LEDGER TAB -->
                    @if ($activeTab === 'budget')
                        <livewire:trip.budget-section :trip="$trip" />
                    @endif

                    <!-- DOCUMENTS TAB -->
                    @if ($activeTab === 'documents')
                        <livewire:trip.documents-section :trip="$trip" />
                    @endif

                    <!-- TRAVELERS TAB -->
                    @if ($activeTab === 'members')
                        <livewire:trip.members-section :trip="$trip" />
                    @endif

                </div>

            </div>

            <!-- RIGHT PANEL: Persistent Collaborative Hub (Polls, Brainstorming Chat, Recent Activity Feed) -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- 1. GROUP DECISIONS (POLLS) -->
                <livewire:trip.polls-sidebar :trip="$trip" />

                <!-- 2. BRAINSTORMING CHAT FEED & 3. WORKSPACE ACTIVITY (RECENT UPDATES) -->
                <livewire:trip.discussion-sidebar :trip="$trip" />

            </div>

        </div>

    </div>
</div>
