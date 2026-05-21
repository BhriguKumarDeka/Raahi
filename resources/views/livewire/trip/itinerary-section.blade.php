<?php

use App\Models\Trip;
use App\Models\ItineraryItem;
use function Livewire\Volt\{state, computed, on};

state([
    'trip',
    'showItineraryModal' => false,
    'itinerary_title' => '',
    'itinerary_desc' => '',
    'itinerary_datetime' => '',
    'itinerary_location' => '',
    'itinerary_duration' => 60,
    'itinerary_cost' => 0,
    'itinerary_category' => 'activities',
]);

on(['trip-updated' => '$refresh']);

$itineraryItems = computed(function () {
    return $this->trip->itineraryItems()->orderBy('datetime', 'asc')->get();
});

$addItineraryItem = function () {
    if (!$this->trip->canEditItinerary(auth()->user())) {
        abort(403);
    }

    $this->validate([
        'itinerary_title' => 'required|string|max:255',
        'itinerary_datetime' => 'required|date',
        'itinerary_category' => 'required|string',
        'itinerary_cost' => 'required|numeric|min:0',
    ]);

    ItineraryItem::create([
        'trip_id' => $this->trip->id,
        'title' => $this->itinerary_title,
        'description' => $this->itinerary_desc,
        'datetime' => $this->itinerary_datetime,
        'location' => $this->itinerary_location,
        'duration_minutes' => $this->itinerary_duration,
        'cost' => $this->itinerary_cost,
        'category' => $this->itinerary_category,
        'added_by' => auth()->id(),
    ]);

    $this->reset(['itinerary_title', 'itinerary_desc', 'itinerary_datetime', 'itinerary_location', 'itinerary_duration', 'itinerary_cost', 'showItineraryModal']);
    $this->dispatch('trip-updated');
};

$deleteItineraryItem = function ($id) {
    if (!$this->trip->canEditItinerary(auth()->user())) {
        abort(403);
    }

    // Scope delete to current trip to prevent IDOR
    $item = $this->trip->itineraryItems()->findOrFail($id);
    $item->delete();
    $this->dispatch('trip-updated');
};

?>

<div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], y: [10, 0] }, { duration: 0.3 }) }">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold font-sans-display text-text-main">Itinerary Plan</h2>
        @if ($trip->canEditItinerary(auth()->user()))
            <button type="button" 
                    wire:click="$set('showItineraryModal', true)"
                    class="flex items-center space-x-2 px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl shadow-none focus:outline-none transition cursor-pointer">
                <i class="ph ph-calendar-plus text-sm"></i>
                <span>Plan a new event</span>
            </button>
        @endif
    </div>

    @if ($this->itineraryItems->isEmpty())
        <div class="bg-bg-primary border border-border-light rounded-3xl p-16 text-center shadow-sm">
            <i class="ph-duotone ph-map-pin text-5xl text-text-muted block mx-auto"></i>
            <h3 class="text-lg font-bold mt-4">No activities planned yet</h3>
            <p class="text-sm text-text-muted mt-1 max-w-sm mx-auto">Start scheduling flights, hotel check-ins, group dinners, and sightseeing tours.</p>
            @if ($trip->canEditItinerary(auth()->user()))
                <button type="button" 
                        wire:click="$set('showItineraryModal', true)"
                        class="mt-6 px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl transition cursor-pointer">
                    Plan first activity
                </button>
            @endif
        </div>
    @else
        @php
            $groupedItems = $this->itineraryItems->groupBy(function($item) {
                return $item->datetime->format('Y-m-d');
            });
        @endphp
        
        <div class="space-y-8 relative before:absolute before:inset-y-0 before:left-8 before:w-0.5 before:bg-border-light">
            @foreach ($groupedItems as $dateStr => $dayItems)
                <div class="space-y-4 relative"
                     x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1] }, { duration: 0.4 }) }">
                    <!-- Date Header badge -->
                    <div class="flex items-center space-x-4 pl-3">
                        <div class="h-10 w-10 rounded-full bg-brand-neutral text-bg-primary flex items-center justify-center font-bold text-xs border-4 border-bg-secondary relative z-10 shadow-sm">
                            <i class="ph ph-calendar"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-brand-neutral font-sans-display">
                            {{ \Carbon\Carbon::parse($dateStr)->format('l, M d, Y') }}
                        </h3>
                    </div>
                    
                    <div class="space-y-4 pl-12">
                        @foreach ($dayItems as $item)
                            <div class="p-5 bg-bg-primary border border-border-light rounded-2xl hover:border-border-card shadow-sm hover:shadow-md transition flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex items-start space-x-3.5">
                                    <span class="p-2.5 bg-bg-secondary border border-border-light rounded-xl text-brand-neutral flex items-center justify-center shrink-0">
                                        @if ($item->category === 'transport')
                                            <i class="ph-bold ph-airplane text-base block"></i>
                                        @elseif ($item->category === 'accommodation')
                                            <i class="ph-bold ph-bed text-base block"></i>
                                        @elseif ($item->category === 'food')
                                            <i class="ph-bold ph-fork-knife text-base block"></i>
                                        @elseif ($item->category === 'activity')
                                            <i class="ph-bold ph-compass text-base block"></i>
                                        @else
                                            <i class="ph-bold ph-dots-three-circle text-base block"></i>
                                        @endif
                                    </span>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-bold text-sm text-text-main">{{ $item->title }}</h4>
                                            <span class="px-2 py-0.5 border border-border-card bg-bg-secondary rounded-full text-[9px] font-bold text-text-muted capitalize">{{ $item->category }}</span>
                                        </div>
                                        <p class="text-xs text-text-muted mt-1 flex items-center space-x-2">
                                            <i class="ph ph-clock"></i>
                                            <span>{{ $item->datetime->format('h:i A') }} @if($item->duration_minutes) ({{ $item->duration_minutes }} mins) @endif</span>
                                            @if($item->location)
                                                <span class="text-border-card">&bull;</span>
                                                <i class="ph ph-map-pin"></i>
                                                <span>{{ $item->location }}</span>
                                            @endif
                                        </p>
                                        @if($item->description)
                                            <p class="text-xs text-text-muted mt-2 bg-bg-secondary/40 border border-border-light rounded-xl p-2.5 leading-relaxed">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4 self-end md:self-auto shrink-0">
                                    @if ($item->cost > 0)
                                        <span class="font-bold text-xs bg-brand-neutral/5 text-brand-neutral border border-brand-neutral/10 px-2.5 py-1 rounded-lg">₹{{ number_format($item->cost, 0) }}</span>
                                    @endif
                                    
                                    @if ($trip->canEditItinerary(auth()->user()))
                                        <button wire:click="deleteItineraryItem({{ $item->id }})" 
                                                wire:confirm="Remove this activity from timeline?"
                                                class="text-text-muted hover:text-red-600 transition p-1.5 bg-bg-secondary hover:bg-red-50 rounded-lg cursor-pointer">
                                            <i class="ph ph-trash text-sm block"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Plan Itinerary Modal -->
    @if ($showItineraryModal)
        <div class="fixed inset-0 bg-bg-secondary/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1] }, { duration: 0.25 }) }">
            <div class="bg-bg-primary border border-border-card rounded-3xl w-full max-w-lg p-6 sm:p-8 shadow-[0_20px_50px_rgba(26,59,43,0.08)] relative"
                 x-init="if(window.Motion) { window.Motion.animate($el, { scale: [0.95, 1], opacity: [0, 1] }, { duration: 0.25 }) }">
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-brand-neutral font-sans-display flex items-center space-x-2">
                        <i class="ph ph-calendar-plus text-lg"></i>
                        <span>Plan a new event</span>
                    </h2>
                    <button type="button" wire:click="$set('showItineraryModal', false)" class="text-text-muted hover:text-text-main transition cursor-pointer p-1">
                        <i class="ph ph-x text-lg block"></i>
                    </button>
                </div>

                <form wire:submit.prevent="addItineraryItem" class="space-y-4">
                    <div>
                        <label for="itinerary_title" class="block text-xs font-bold text-text-main">Event Title</label>
                        <input type="text" id="itinerary_title" wire:model="itinerary_title" placeholder="e.g. Flight to Bali, Hotel Check-in"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        @error('itinerary_title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="itinerary_category" class="block text-xs font-bold text-text-main">Category</label>
                            <select id="itinerary_category" wire:model="itinerary_category"
                                    class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                <option value="activities">Activities</option>
                                <option value="transport">Transport</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="food">Food & Dining</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                        </div>

                        <div>
                            <label for="itinerary_datetime" class="block text-xs font-bold text-text-main">Date & Time</label>
                            <input type="datetime-local" id="itinerary_datetime" wire:model="itinerary_datetime"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                            @error('itinerary_datetime') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="itinerary_location" class="block text-xs font-bold text-text-main">Location (optional)</label>
                            <input type="text" id="itinerary_location" wire:model="itinerary_location" placeholder="e.g. Ngurah Rai Airport"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        </div>

                        <div>
                            <label for="itinerary_duration" class="block text-xs font-bold text-text-main">Duration (minutes)</label>
                            <input type="number" id="itinerary_duration" wire:model="itinerary_duration"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        </div>
                    </div>

                    <div>
                        <label for="itinerary_cost" class="block text-xs font-bold text-text-main">Cost (₹, optional)</label>
                        <input type="number" id="itinerary_cost" wire:model="itinerary_cost" placeholder="0"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                    </div>

                    <div>
                        <label for="itinerary_desc" class="block text-xs font-bold text-text-main">Description (optional)</label>
                        <textarea id="itinerary_desc" wire:model="itinerary_desc" rows="3" placeholder="Additional details..."
                                  class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral"></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" wire:click="$set('showItineraryModal', false)" 
                                class="px-5 py-2.5 border border-border-card rounded-xl text-xs font-bold text-text-main hover:bg-bg-secondary transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl transition cursor-pointer">
                            Plan event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
