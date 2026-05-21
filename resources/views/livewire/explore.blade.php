<?php

use function Livewire\Volt\{state, computed};
use App\Models\Trip;
use App\Models\ItineraryItem;
use Carbon\Carbon;

state([
    'selectedStyle' => 'All',
    'showPreviewModal' => false,
    'previewKey' => '',
    'searchQuery' => '',
]);

\Livewire\Volt\mount(function () {
    if (request()->has('preview')) {
        $this->previewKey = request('preview');
        $this->showPreviewModal = true;
    }
});

$destinations = computed(function () {
    $items = [
        'bali' => [
            'name' => 'Summer in Bali',
            'destination' => 'Bali, Indonesia',
            'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80',
            'tags' => ['Adventure', 'Relaxed', 'Beach Escapes'],
            'budget' => 1200,
            'description' => 'Sun-soaked shorelines, ancient temples, active volcanoes, and lush green rice terraces.',
            'itinerary' => [
                ['day' => 1, 'time' => '14:00', 'title' => 'Arrive & Check-in', 'location' => 'Seminyak Hotel', 'desc' => 'Arrive in Seminyak, check into our resort, and unwind by the beach.'],
                ['day' => 1, 'time' => '18:30', 'title' => 'Sunset Seafood Dinner', 'location' => 'Jimbaran Bay', 'desc' => 'Enjoy grilled snapper, prawns, and crabs fresh from the sea at Jimbaran.'],
                ['day' => 2, 'time' => '09:00', 'title' => 'Uluwatu Temple Cliff Tour', 'location' => 'Uluwatu Temple', 'desc' => 'Explore the iconic sea temple perched on a steep cliff, home to wild macaques.'],
                ['day' => 2, 'time' => '17:00', 'title' => 'Kecak Fire Dance Performance', 'location' => 'Uluwatu Ampitheater', 'desc' => 'Watch the dramatic dance telling the story of Ramayana by the setting sun.'],
                ['day' => 3, 'time' => '08:00', 'title' => 'White Water Rafting', 'location' => 'Ayung River, Ubud', 'desc' => 'Thrilling rafting adventure down Bali\'s longest river through lush valleys.'],
                ['day' => 3, 'time' => '14:30', 'title' => 'Tegalalang Rice Terraces', 'location' => 'Ubud Rice Fields', 'desc' => 'Walk through beautiful cascading green fields and take photos on the giant swings.'],
                ['day' => 4, 'time' => '07:00', 'title' => 'Snorkeling Cruise', 'location' => 'Nusa Penida', 'desc' => 'Boat trip to Nusa Penida to swim with massive Manta Rays and explore coral gardens.'],
            ],
        ],
        'kyoto' => [
            'name' => 'Kyoto Cultural Discovery',
            'destination' => 'Kyoto, Japan',
            'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=800&q=80',
            'tags' => ['Cultural', 'Relaxed', 'City Breaks'],
            'budget' => 1800,
            'description' => 'Explore ancient Buddhist temples, traditional wooden houses, imperial palaces, and Shinto shrines.',
            'itinerary' => [
                ['day' => 1, 'time' => '15:00', 'title' => 'Hotel Check-in & Gion Walk', 'location' => 'Traditional Ryokan, Gion', 'desc' => 'Settle in and stroll through the historic streets, searching for Geishas.'],
                ['day' => 1, 'time' => '19:00', 'title' => 'Kyoto Kaiseki Dining', 'location' => 'Pontocho Alley', 'desc' => 'Multi-course traditional Japanese dinner beside the Kamogawa River.'],
                ['day' => 2, 'time' => '08:30', 'title' => 'Fushimi Inari Shrine Hike', 'location' => 'Fushimi Inari', 'desc' => 'Walk up the mountain through thousands of brilliant orange Torii gates.'],
                ['day' => 2, 'time' => '14:00', 'title' => 'Kiyomizu-dera Temple', 'location' => 'Eastern Kyoto', 'desc' => 'Historic temple wooden stage offering sweeping views of Kyoto and cherry blossoms.'],
                ['day' => 3, 'time' => '09:30', 'title' => 'Kinkaku-ji (Golden Pavilion)', 'location' => 'Northern Kyoto', 'desc' => 'Breathtaking Zen temple covered in brilliant gold leaf overlooking a mirror pond.'],
                ['day' => 3, 'time' => '13:00', 'title' => 'Arashiyama Bamboo Grove Walk', 'location' => 'Arashiyama', 'desc' => 'Walk through towering stalks of green bamboo whispering in the wind.'],
                ['day' => 4, 'time' => '10:00', 'title' => 'Matcha Tea Ceremony', 'location' => 'Tea House near Nijo Castle', 'desc' => 'Learn the Zen art of brewing and tasting ceremonial green tea.'],
            ],
        ],
        'patagonia' => [
            'name' => 'Patagonia Adventure & Hiking',
            'destination' => 'Patagonia, Argentina',
            'image' => 'https://images.unsplash.com/photo-1517411032315-54ef2cb783bb?auto=format&fit=crop&w=800&q=80',
            'tags' => ['Adventure', 'Mountain Treks', 'Luxury'],
            'budget' => 2500,
            'description' => 'Jagged granite peaks, massive glaciers, pristine turquoise lakes, and wild windswept steppes.',
            'itinerary' => [
                ['day' => 1, 'time' => '13:00', 'title' => 'Arrive in El Calafate', 'location' => 'Lakeside Lodge', 'desc' => 'Check in, check hiking gear, and enjoy views of the glacier lake.'],
                ['day' => 1, 'time' => '19:30', 'title' => 'Patagonian Lamb Barbecue', 'location' => 'Traditional Estancia', 'desc' => 'Savor slow-roasted cordero patagónico around an open fire pit.'],
                ['day' => 2, 'time' => '08:00', 'title' => 'Perito Moreno Glacier Trekking', 'location' => 'Perito Moreno Glacier', 'desc' => 'Put on crampons and hike across the ice fields of one of the world\'s active glaciers.'],
                ['day' => 2, 'time' => '16:00', 'title' => 'Boat Cruise along Ice Walls', 'location' => 'Lake Argentino', 'desc' => 'Catamaran cruise getting up close to the massive blue ice cliffs.'],
                ['day' => 3, 'time' => '07:30', 'title' => 'Mount Fitz Roy Viewpoint Hike', 'location' => 'El Chaltén Trail', 'desc' => 'Full-day hike through yellow forests to Laguna de los Tres for iconic mountain views.'],
                ['day' => 4, 'time' => '09:00', 'title' => 'Lake Desert Kayak Tour', 'location' => 'Lago del Desierto', 'desc' => 'Paddle turquoise waters reflecting hanging glaciers and high beech trees.'],
            ],
        ],
        'paris' => [
            'name' => 'Paris Culture & Fine Dining',
            'destination' => 'Paris, France',
            'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800&q=80',
            'tags' => ['Cultural', 'Luxury', 'City Breaks'],
            'budget' => 3000,
            'description' => 'World-famous art, history, fashion, gastronomy, and the romantic Seine River.',
            'itinerary' => [
                ['day' => 1, 'time' => '14:00', 'title' => 'Boutique Hotel Check-in', 'location' => 'Le Marais', 'desc' => 'Settle into our historic hotel in the heart of Paris\'s trendiest district.'],
                ['day' => 1, 'time' => '18:00', 'title' => 'Seine River Sunset Cruise', 'location' => 'Bateaux-Mouches', 'desc' => 'Glass-topped boat ride as monuments begin to light up at dusk.'],
                ['day' => 2, 'time' => '09:00', 'title' => 'Louvre Museum Tour', 'location' => 'Louvre Palace', 'desc' => 'Guided access to see the Mona Lisa, Venus de Milo, and masterpieces.'],
                ['day' => 2, 'time' => '15:00', 'title' => 'Walk down Champs-Élysées', 'location' => 'Arc de Triomphe', 'desc' => 'Stroll past designer shops and climb the Arc for panoramic views.'],
                ['day' => 3, 'time' => '10:00', 'title' => 'Macaron Masterclass', 'location' => 'French Pastry School', 'desc' => 'Learn the secret to baking delicate, colorful French macarons with a chef.'],
                ['day' => 3, 'time' => '19:30', 'title' => 'Michelin Dining Experience', 'location' => 'Eiffel Tower Restaurant', 'desc' => 'Multi-course gourmet dinner with sweeping views of the city lights.'],
                ['day' => 4, 'time' => '09:00', 'title' => 'Palace of Versailles Tour', 'location' => 'Versailles Palace', 'desc' => 'Walk through the Hall of Mirrors and royal gardens of the Sun King.'],
            ],
        ],
    ];

    foreach ($items as $key => $item) {
        $items[$key]['image'] = \App\Services\PexelsService::getTripImage($item['destination'], $item['image']);
    }

    return $items;
});

$filteredDestinations = computed(function () {
    $dests = $this->destinations;

    // Filter by tag style
    if ($this->selectedStyle !== 'All') {
        $dests = array_filter($dests, function ($dest) {
            return in_array($this->selectedStyle, $dest['tags']);
        });
    }

    // Filter by search query
    if (!empty($this->searchQuery)) {
        $query = strtolower($this->searchQuery);
        $dests = array_filter($dests, function ($dest) use ($query) {
            return str_contains(strtolower($dest['name']), $query) ||
                str_contains(strtolower($dest['destination']), $query) ||
                str_contains(strtolower($dest['description']), $query) ||
                collect($dest['tags'])->contains(fn($tag) => str_contains(strtolower($tag), $query));
        });
    }

    return $dests;
});

$openPreview = function ($key) {
    $this->previewKey = $key;
    $this->showPreviewModal = true;
};

$cloneTrip = function ($key) {
    $template = $this->destinations[$key] ?? null;
    if (!$template) return;

    $user = auth()->user();

    // Create new Trip
    $trip = Trip::create([
        'name' => $template['name'],
        'destination' => $template['destination'],
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(34),
        'description' => $template['description'],
        'budget_estimate' => $template['budget'],
        'creator_id' => $user->id,
    ]);

    // Attach as Organizer
    $trip->users()->attach($user->id, ['role' => 'organizer']);

    // Seed itinerary items
    $start = Carbon::parse($trip->start_date);
    foreach ($template['itinerary'] as $item) {
        $itemDate = $start->copy()->addDays($item['day'] - 1)->toDateString();
        $itemDatetime = $itemDate . ' ' . $item['time'] . ':00';

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'title' => $item['title'],
            'description' => $item['desc'],
            'datetime' => $itemDatetime,
            'location' => $item['location'],
            'duration_minutes' => 120,
            'cost' => 0.00,
            'category' => 'other',
            'added_by' => $user->id,
        ]);
    }

    return redirect()->route('trips.show', $trip->id)
        ->with('status', 'Trip cloned successfully! Welcome to your new workspace.');
};

$hasRecommendationMatch = function ($tags) {
    $user = auth()->user();
    if (!$user || !$user->travel_style) return false;
    return count(array_intersect($tags, $user->travel_style)) > 0;
};

?>

<div class="py-12 bg-bg-secondary min-h-screen text-text-main font-sans-display">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8 pb-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight font-serif-display text-text-main">Explore Curated Trips</h1>
                <p class="text-text-muted text-xs mt-1.5 font-medium">Discover popular destinations matching your profile and clone them with one click.</p>
            </div>

            <!-- Search bar with suggestions -->
            <div class="relative w-full md:w-80"
                x-data="{
                     query: @entangle('searchQuery'),
                     showSuggestions: false,
                     allDestinations: @js(collect($this->destinations)->map(fn($d, $k) => ['key' => $k, 'name' => $d['name'], 'destination' => $d['destination']])->values()->toArray()),
                     get filtered() {
                         if (!this.query || this.query.length < 1) return [];
                         let q = this.query.toLowerCase();
                         return this.allDestinations.filter(d =>
                             d.name.toLowerCase().includes(q) || d.destination.toLowerCase().includes(q)
                         );
                     },
                     selectSuggestion(name) {
                         this.query = name;
                         this.showSuggestions = false;
                     }
                 }"
                @click.outside="showSuggestions = false">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-muted">
                    <i class="ph ph-magnifying-glass"></i>
                </div>
                <input type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    x-model="query"
                    @focus="showSuggestions = true"
                    @input="showSuggestions = true"
                    placeholder="Search destinations, tags..."
                    class="block w-full pl-10 pr-4 py-2.5 bg-bg-primary border border-border-card rounded-full text-xs placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral shadow-none transition">

                <!-- Search Suggestions Dropdown -->
                <div x-show="showSuggestions && filtered.length > 0"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="absolute z-30 top-full mt-2 w-full bg-bg-primary border border-border-card rounded-xl shadow-lg overflow-hidden">
                    <template x-for="item in filtered" :key="item.key">
                        <button @click="selectSuggestion(item.name)"
                            class="w-full text-left px-4 py-2.5 text-xs hover:bg-bg-secondary transition flex items-center gap-2.5 cursor-pointer">
                            <i class="ph ph-map-pin text-brand-neutral text-sm"></i>
                            <div>
                                <span class="font-semibold text-text-main" x-text="item.name"></span>
                                <span class="text-text-muted ml-1.5" x-text="item.destination"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap gap-2 mb-8 border-b border-border-light pb-6">
            <button wire:click="$set('selectedStyle', 'All')"
                class="px-4 py-2 border rounded-full text-xs font-semibold cursor-pointer transition-all {{ $selectedStyle === 'All' ? 'bg-brand-neutral border-brand-neutral text-bg-primary' : 'bg-bg-primary border-border-card text-text-muted hover:border-text-main hover:text-text-main' }}">
                All Destinations
            </button>
            @foreach(['Adventure', 'Relaxed', 'Cultural', 'Budget', 'Luxury'] as $style)
            <button wire:click="$set('selectedStyle', '{{ $style }}')"
                class="px-4 py-2 border rounded-full text-xs font-semibold cursor-pointer transition-all {{ $selectedStyle === $style ? 'bg-brand-neutral border-brand-neutral text-bg-primary' : 'bg-bg-primary border-border-card text-text-muted hover:border-text-main hover:text-text-main' }}">
                {{ $style }}
            </button>
            @endforeach
        </div>

        <!-- Destinations Grid -->
        @if (empty($this->filteredDestinations))
        <div class="bg-bg-primary border border-border-light rounded-2xl p-16 text-center shadow-none col-span-2 space-y-3">
            <i class="ph ph-magnifying-glass text-4xl text-text-muted"></i>
            <h3 class="mt-4 text-lg font-bold">No destinations found</h3>
            <p class="mt-1 text-xs text-text-muted">Try refining your search query or choosing another style filter.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($this->filteredDestinations as $key => $dest)
            <div class="bg-bg-primary border border-border-light rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition duration-200">
                <div class="p-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-text-main leading-tight">{{ $dest['name'] }}</h3>
                        <p class="text-sm text-text-muted mt-1">{{ $dest['destination'] }}</p>
                    </div>

                    <div class="relative mt-6 rounded-3xl overflow-hidden group">
                        <img src="{{ $dest['image'] }}"
                            alt="{{ $dest['destination'] }}"
                            class="h-56 w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute left-5 right-5 bottom-5 rounded-full bg-black/60 text-white px-4 py-3 flex items-center justify-between gap-3 text-xs font-bold shadow-lg">
                            <span class="flex items-center gap-1.5"><i class="ph ph-clock"></i>{{ count($dest['itinerary']) }} Days</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-globe-hemisphere-east"></i>{{ $dest['tags'][0] ?? 'Curated' }}</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-calendar-blank"></i>Template</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-border-light space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-bold text-text-main">Accommodation</span>
                            <span class="text-text-muted text-right">Curated stays</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-bold text-text-main">Transport</span>
                            <span class="text-text-muted text-right">Local transfers</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-bold text-text-main">Style</span>
                            <span class="text-text-muted text-right">{{ implode(' & ', array_slice($dest['tags'], 0, 2)) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-bg-secondary/70 border-t border-border-light px-6 py-5 flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] uppercase tracking-wider font-extrabold text-text-muted">Starting at</span>
                        <span class="text-2xl font-extrabold text-text-main">{{ "\u{20B9}" }}{{ number_format($dest['budget'], 0) }}</span>
                        <span class="text-xs text-text-muted"> / person</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="openPreview('{{ $key }}')" title="View Itinerary" class="h-11 w-11 rounded-full border border-border-card bg-bg-primary text-text-main hover:border-brand-neutral hover:text-brand-neutral flex items-center justify-center transition cursor-pointer">
                            <i class="ph ph-eye text-base"></i>
                        </button>
                        <button wire:click="cloneTrip('{{ $key }}')" title="Clone to Workspace" class="h-12 w-12 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-md cursor-pointer">
                            <i class="ph-bold ph-plus text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Preview Modal -->
    @if($showPreviewModal && !empty($previewKey))
    @php
    $modalDest = $this->destinations()[$previewKey];
    $totalBudget = $modalDest['budget'];
    $budgetBreakdown = [
    ['label' => 'Accommodation', 'icon' => 'ph-buildings', 'percent' => 35, 'amount' => round($totalBudget * 0.35)],
    ['label' => 'Transport', 'icon' => 'ph-airplane-tilt', 'percent' => 25, 'amount' => round($totalBudget * 0.25)],
    ['label' => 'Activities', 'icon' => 'ph-compass', 'percent' => 25, 'amount' => round($totalBudget * 0.25)],
    ['label' => 'Food & Drinks', 'icon' => 'ph-fork-knife', 'percent' => 15, 'amount' => round($totalBudget * 0.15)],
    ];
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
        <div class="bg-bg-primary border border-border-light w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden p-8 animate-fade-in max-h-[85vh] flex flex-col justify-between">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                <div>
                    <span class="text-xs text-text-muted font-bold uppercase tracking-wider">{{ $modalDest['destination'] }}</span>
                    <h2 class="text-xl font-bold text-text-main mt-0.5">{{ $modalDest['name'] }} Itinerary</h2>
                </div>
                <button wire:click="$set('showPreviewModal', false)"
                    class="text-text-muted hover:text-text-main transition p-1.5 cursor-pointer">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto space-y-6 pr-2">
                <p class="text-sm text-text-muted italic">{{ $modalDest['description'] }}</p>

                <!-- Budget Breakdown -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-3 flex items-center gap-1.5">
                        <i class="ph ph-wallet text-brand-neutral text-sm"></i>
                        Budget Breakdown
                        <span class="ml-auto text-text-main font-extrabold text-sm normal-case tracking-normal">{{ "\u{20B9}" }}{{ number_format($totalBudget, 0) }} total</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($budgetBreakdown as $segment)
                        <div class="bg-bg-secondary border border-border-light rounded-xl p-3 text-center space-y-1.5">
                            <div class="w-8 h-8 mx-auto rounded-full bg-brand-neutral/10 flex items-center justify-center">
                                <i class="ph {{ $segment['icon'] }} text-brand-neutral text-base"></i>
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-text-muted">{{ $segment['label'] }}</p>
                            <p class="text-sm font-extrabold text-text-main">{{ "\u{20B9}" }}{{ number_format($segment['amount'], 0) }}</p>
                            <p class="text-[10px] text-text-muted">{{ $segment['percent'] }}%</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Itinerary Timeline -->
                <div class="space-y-4 relative border-l-2 border-border-light ml-4 pl-6 py-2">
                    @foreach($modalDest['itinerary'] as $item)
                    <div class="relative">
                        <!-- Day marker -->
                        <div class="absolute -left-8.75 top-1.5 bg-bg-primary border-2 border-brand-neutral text-text-main h-6 w-6 rounded-full flex items-center justify-center text-[10px] font-bold">
                            D{{ $item['day'] }}
                        </div>
                        <div class="p-4 bg-bg-secondary border border-border-light rounded-xl space-y-1">
                            <div class="flex justify-between items-center text-xs font-semibold">
                                <span class="text-brand-neutral">{{ $item['time'] }}</span>
                                <span class="text-text-muted">{{ $item['location'] }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-text-main">{{ $item['title'] }}</h4>
                            <p class="text-xs text-text-muted leading-relaxed mt-1">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                <!-- Share Button in Modal -->
                <div x-data="{ copied: false }" class="relative mr-auto">
                    <button @click="
                            navigator.clipboard.writeText(window.location.origin + '/explore?preview={{ $previewKey }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        " class="px-4 py-2.5 border border-border-card rounded-xl text-xs font-semibold text-text-main hover:bg-bg-secondary transition cursor-pointer flex items-center gap-1.5">
                        <i class="ph ph-share-network text-sm"></i>
                        <span x-text="copied ? 'Copied!' : 'Share'"></span>
                    </button>
                </div>

                <button wire:click="$set('showPreviewModal', false)"
                    class="px-5 py-2.5 border border-border-card rounded-xl text-xs font-semibold text-text-main hover:bg-bg-secondary transition cursor-pointer">
                    Close
                </button>
                <button wire:click="cloneTrip('{{ $previewKey }}')"
                    class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-semibold rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="ph ph-copy text-sm"></i>
                    <span>Clone Itinerary</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
