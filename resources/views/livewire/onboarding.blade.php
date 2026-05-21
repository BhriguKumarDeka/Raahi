<?php

use function Livewire\Volt\{state};

state([
    'step' => 1,
    'travelStyle' => [],
    'planningRole' => '',
    'activityInterests' => [],
    'preferredDestinations' => '',
]);

$nextStep = function () {
    if ($this->step < 5) {
        $this->step++;
    }
};

$prevStep = function () {
    if ($this->step > 1) {
        $this->step--;
    }
};

$toggleStyle = function ($style) {
    if (in_array($style, $this->travelStyle)) {
        $this->travelStyle = array_diff($this->travelStyle, [$style]);
    } else {
        $this->travelStyle[] = $style;
    }
};

$toggleInterest = function ($interest) {
    if (in_array($interest, $this->activityInterests)) {
        $this->activityInterests = array_diff($this->activityInterests, [$interest]);
    } else {
        $this->activityInterests[] = $interest;
    }
};

$completeOnboarding = function () {
    $user = auth()->user();
    
    // Parse preferred destinations into array
    $destinations = array_filter(array_map('trim', explode(',', $this->preferredDestinations)));

    $user->update([
        'travel_style' => $this->travelStyle,
        'budget_preference' => $this->planningRole, // we'll use this or map it to budget/style
        'activity_interests' => $this->activityInterests,
        'preferred_destinations' => $destinations,
        'onboarded' => true,
    ]);

    return redirect()->route('dashboard');
};

?>

<div class="min-h-screen bg-bg-secondary flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8 font-sans-display text-text-main relative overflow-hidden">
    <!-- Ambient glow -->
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-brand-neutral/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-brand-neutral/5 blur-[120px] pointer-events-none"></div>

    <div x-data x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], y: [15, 0] }, { duration: 0.5, easing: 'ease-out' }) }" class="max-w-xl mx-auto w-full bg-bg-primary border border-border-card rounded-3xl shadow-[0_15px_50px_rgba(26,59,43,0.03)] p-8 sm:p-12 my-auto z-10">
        <!-- Progress Bar -->
        <div class="w-full bg-border-light h-1 rounded-full mb-8 overflow-hidden">
            <div class="bg-brand-neutral h-full transition-all duration-300 ease-out" style="width: {{ ($step / 5) * 100 }}%"></div>
        </div>

        <!-- Step 1: Welcome -->
        @if ($step === 1)
            <div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], x: [10, 0] }, { duration: 0.3 }) }">
                <h1 class="text-3xl font-extrabold tracking-tight font-serif-display text-text-main">Welcome to Raahi</h1>
                <p class="text-text-muted text-sm leading-relaxed">
                    Raahi is a living, collaborative workspace designed to simplify group travel planning. Discover destinations, coordinate schedules, vote on activities, and track shared expenses with ease.
                </p>
                <div class="pt-4 space-y-2">
                    <p class="text-xs text-text-muted uppercase font-bold tracking-wider mb-2">Let's get started</p>
                    <p class="text-xs text-text-muted">We will ask you a few quick questions to customize your planning experience.</p>
                </div>
            </div>
        @endif

        <!-- Step 2: Travel Style -->
        @if ($step === 2)
            <div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], x: [10, 0] }, { duration: 0.3 }) }">
                <div>
                    <h2 class="text-2xl font-bold font-serif-display tracking-tight">What's your travel style?</h2>
                    <p class="text-xs text-text-muted mt-1.5 font-medium">Select all that apply to help us suggest itineraries.</p>
                </div>
 
                <div class="grid grid-cols-2 gap-4">
                    @foreach(['Adventure', 'Relaxed', 'Cultural', 'Budget', 'Luxury'] as $style)
                        <button type="button" 
                                wire:click="toggleStyle('{{ $style }}')"
                                class="p-4 border rounded-2xl text-left transition-all duration-200 focus:outline-none cursor-pointer {{ in_array($style, $travelStyle) ? 'border-brand-neutral bg-bg-secondary font-bold text-brand-neutral' : 'border-border-card bg-bg-primary hover:border-text-muted text-text-main' }}">
                            <div class="flex justify-between items-center">
                                <span class="text-sm">{{ $style }}</span>
                                @if (in_array($style, $travelStyle))
                                    <i class="ph ph-check-circle text-base text-brand-neutral"></i>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Step 3: Planning Role -->
        @if ($step === 3)
            <div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], x: [10, 0] }, { duration: 0.3 }) }">
                <div>
                    <h2 class="text-2xl font-bold font-serif-display tracking-tight">What is your typical planning role?</h2>
                    <p class="text-xs text-text-muted mt-1.5 font-medium">Choose the one that best fits your travel habits.</p>
                </div>
 
                <div class="space-y-3">
                    @foreach([
                        'Organizer' => 'You love structuring plans, finding accommodation, and managing the group.',
                        'Contributor' => 'You participate actively, suggest items, and help split tasks.',
                        'Passive' => 'You prefer to sit back, let others plan, and go with the flow.'
                    ] as $role => $desc)
                        <button type="button" 
                                wire:click="$set('planningRole', '{{ $role }}')"
                                class="w-full p-4 border rounded-2xl text-left transition-all duration-200 focus:outline-none flex flex-col cursor-pointer {{ $planningRole === $role ? 'border-brand-neutral bg-bg-secondary ring-1 ring-brand-neutral' : 'border-border-card bg-bg-primary hover:border-text-muted' }}">
                            <span class="font-bold text-sm text-text-main flex items-center gap-1.5">
                                @if($role === 'Organizer') <i class="ph ph-crown text-base text-brand-neutral"></i> @endif
                                @if($role === 'Contributor') <i class="ph ph-handshake text-base text-brand-neutral"></i> @endif
                                @if($role === 'Passive') <i class="ph ph-compass text-base text-brand-neutral"></i> @endif
                                <span>{{ $role }}</span>
                            </span>
                            <span class="text-xs text-text-muted mt-1.5 leading-relaxed">{{ $desc }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Step 4: Activity Interests -->
        @if ($step === 4)
            <div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], x: [10, 0] }, { duration: 0.3 }) }">
                <div>
                    <h2 class="text-2xl font-bold font-serif-display tracking-tight">What activities interest you?</h2>
                    <p class="text-xs text-text-muted mt-1.5 font-medium">Select topics you enjoy during trips.</p>
                </div>
 
                <div class="flex flex-wrap gap-2">
                    @foreach(['Hiking', 'Sightseeing', 'Food & Dining', 'Beaches', 'Shopping', 'Museums', 'Nightlife', 'Wellness'] as $interest)
                        <button type="button" 
                                wire:click="toggleInterest('{{ $interest }}')"
                                class="px-4 py-2 border rounded-full text-xs font-semibold cursor-pointer transition-all duration-200 focus:outline-none {{ in_array($interest, $activityInterests) ? 'border-brand-neutral bg-brand-neutral text-bg-primary font-bold' : 'border-border-card bg-bg-primary hover:border-text-muted text-text-muted' }}">
                            {{ $interest }}
                        </button>
                    @endforeach
                </div>
 
                <div class="pt-4 border-t border-border-light">
                    <label for="destinations" class="block text-xs font-bold text-text-main uppercase tracking-wider">Dream destinations (optional)</label>
                    <input type="text" 
                           id="destinations"
                           wire:model="preferredDestinations"
                           placeholder="e.g. Paris, Tokyo, Bali (comma separated)"
                           class="mt-2 block w-full px-4 py-3 border border-border-card rounded-xl shadow-none focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral text-xs bg-bg-primary placeholder-text-muted">
                </div>
            </div>
        @endif

        <!-- Step 5: Final Review -->
        @if ($step === 5)
            <div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], x: [10, 0] }, { duration: 0.3 }) }">
                <div class="text-center py-4 space-y-3">
                    <i class="ph-duotone ph-check-circle text-5xl text-brand-neutral mx-auto"></i>
                    <h2 class="text-2xl font-bold font-serif-display tracking-tight mt-4">You're all set!</h2>
                    <p class="text-xs text-text-muted mt-1 font-medium">Your preferences are saved. You can change them anytime in your profile.</p>
                </div>
 
                <div class="bg-bg-secondary p-5 border border-border-card rounded-2xl space-y-3 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-text-muted font-medium">Travel Styles:</span>
                        <span class="font-bold text-text-main">{{ count($travelStyle) > 0 ? implode(', ', $travelStyle) : 'None selected' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-text-muted font-medium">Planning Role:</span>
                        <span class="font-bold text-text-main">{{ $planningRole ?: 'Not selected' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-text-muted font-medium">Interests:</span>
                        <span class="font-bold text-text-main">{{ count($activityInterests) > 0 ? implode(', ', $activityInterests) : 'None selected' }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center mt-12 pt-6 border-t border-border-light">
            @if ($step > 1)
                <button type="button" 
                        wire:click="prevStep"
                        class="px-6 py-2.5 border border-border-card text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-bg-secondary focus:outline-none transition-all cursor-pointer">
                    Back
                </button>
            @else
                <div></div>
            @endif
 
            @if ($step < 5)
                <button type="button" 
                        wire:click="nextStep"
                        class="px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl focus:outline-none transition-all shadow-none cursor-pointer">
                    Next
                </button>
            @else
                <button type="button" 
                        wire:click="completeOnboarding"
                        class="px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl focus:outline-none transition-all shadow-none cursor-pointer">
                    Start Planning
                </button>
            @endif
        </div>
    </div>
</div>
