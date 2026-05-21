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

<div class="min-h-screen bg-bg-secondary flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8 font-sans text-text-main">
    <div class="max-w-xl mx-auto w-full bg-bg-primary border border-border-light rounded-2xl shadow-sm p-8 sm:p-12 my-auto">
        <!-- Progress Bar -->
        <div class="w-full bg-border-light h-1 rounded-full mb-8 overflow-hidden">
            <div class="bg-brand-neutral h-full transition-all duration-300 ease-out" style="width: {{ ($step / 5) * 100 }}%"></div>
        </div>

        <!-- Step 1: Welcome -->
        @if ($step === 1)
            <div class="space-y-6">
                <h1 class="text-3xl font-extrabold tracking-tight text-text-main">Welcome to Raahi</h1>
                <p class="text-text-muted leading-relaxed">
                    Raahi is a collaborative workspace designed to simplify group travel planning. Discover destinations, coordinate schedules, vote on activities, and track shared expenses with ease.
                </p>
                <div class="pt-4">
                    <p class="text-xs text-text-muted uppercase font-semibold tracking-wider mb-2">Let's get started</p>
                    <p class="text-sm text-text-muted">We will ask you a few quick questions to customize your planning experience.</p>
                </div>
            </div>
        @endif

        <!-- Step 2: Travel Style -->
        @if ($step === 2)
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">What's your travel style?</h2>
                    <p class="text-sm text-text-muted mt-1">Select all that apply to help us suggest itineraries.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach(['Adventure', 'Relaxed', 'Cultural', 'Budget', 'Luxury'] as $style)
                        <button type="button" 
                                wire:click="toggleStyle('{{ $style }}')"
                                class="p-4 border rounded-xl text-left transition-all duration-200 focus:outline-none {{ in_array($style, $travelStyle) ? 'border-brand-neutral bg-bg-secondary font-semibold' : 'border-border-card bg-bg-primary hover:border-text-muted' }}">
                            <div class="flex justify-between items-center">
                                <span>{{ $style }}</span>
                                @if (in_array($style, $travelStyle))
                                    <svg class="h-5 w-5 text-brand-neutral" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Step 3: Planning Role -->
        @if ($step === 3)
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">What is your typical planning role?</h2>
                    <p class="text-sm text-text-muted mt-1">Choose the one that best fits your travel habits.</p>
                </div>

                <div class="space-y-3">
                    @foreach([
                        'Organizer' => 'You love structuring plans, finding accommodation, and managing the group.',
                        'Contributor' => 'You participate actively, suggest items, and help split tasks.',
                        'Passive' => 'You prefer to sit back, let others plan, and go with the flow.'
                    ] as $role => $desc)
                        <button type="button" 
                                wire:click="$set('planningRole', '{{ $role }}')"
                                class="w-full p-4 border rounded-xl text-left transition-all duration-200 focus:outline-none flex flex-col {{ $planningRole === $role ? 'border-brand-neutral bg-bg-secondary' : 'border-border-card bg-bg-primary hover:border-text-muted' }}">
                            <span class="font-semibold text-text-main">{{ $role }}</span>
                            <span class="text-xs text-text-muted mt-1">{{ $desc }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Step 4: Activity Interests -->
        @if ($step === 4)
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">What activities interest you?</h2>
                    <p class="text-sm text-text-muted mt-1">Select topics you enjoy during trips.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach(['Hiking', 'Sightseeing', 'Food & Dining', 'Beaches', 'Shopping', 'Museums', 'Nightlife', 'Wellness'] as $interest)
                        <button type="button" 
                                wire:click="toggleInterest('{{ $interest }}')"
                                class="px-4 py-2 border rounded-full text-sm transition-all duration-200 focus:outline-none {{ in_array($interest, $activityInterests) ? 'border-brand-neutral bg-brand-neutral text-bg-primary' : 'border-border-card bg-bg-primary hover:border-text-muted text-text-main' }}">
                            {{ $interest }}
                        </button>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-border-light">
                    <label for="destinations" class="block text-sm font-semibold text-text-main">Dream destinations (optional)</label>
                    <input type="text" 
                           id="destinations"
                           wire:model="preferredDestinations"
                           placeholder="e.g. Paris, Tokyo, Bali (comma separated)"
                           class="mt-2 block w-full px-4 py-3 border border-border-card rounded-xl shadow-none focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral sm:text-sm bg-bg-primary">
                </div>
            </div>
        @endif

        <!-- Step 5: Final Review -->
        @if ($step === 5)
            <div class="space-y-6">
                <div class="text-center py-4">
                    <svg class="mx-auto h-12 w-12 text-text-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-2xl font-bold tracking-tight mt-4">You're all set!</h2>
                    <p class="text-sm text-text-muted mt-1">Your preferences are saved. You can change them anytime in your profile.</p>
                </div>

                <div class="bg-bg-secondary p-4 rounded-xl space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Travel Styles:</span>
                        <span class="font-medium">{{ count($travelStyle) > 0 ? implode(', ', $travelStyle) : 'None selected' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Planning Role:</span>
                        <span class="font-medium">{{ $planningRole ?: 'Not selected' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Interests:</span>
                        <span class="font-medium">{{ count($activityInterests) > 0 ? implode(', ', $activityInterests) : 'None selected' }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center mt-12 pt-6 border-t border-border-light">
            @if ($step > 1)
                <button type="button" 
                        wire:click="prevStep"
                        class="px-6 py-2 border border-border-card text-sm font-semibold rounded-xl hover:bg-bg-secondary focus:outline-none transition-all">
                    Back
                </button>
            @else
                <div></div>
            @endif

            @if ($step < 5)
                <button type="button" 
                        wire:click="nextStep"
                        class="px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-sm font-semibold rounded-xl focus:outline-none transition-all shadow-none">
                    Next
                </button>
            @else
                <button type="button" 
                        wire:click="completeOnboarding"
                        class="px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-sm font-semibold rounded-xl focus:outline-none transition-all shadow-none">
                    Start Planning
                </button>
            @endif
        </div>
    </div>
</div>
