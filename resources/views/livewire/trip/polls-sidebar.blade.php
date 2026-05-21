<?php

use App\Models\Trip;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Support\Str;
use function Livewire\Volt\{state, computed, on};

state([
    'trip',
    'showPollModal' => false,
    'poll_title' => '',
    'poll_desc' => '',
    'poll_type' => 'general',
    'poll_options' => ['', ''], // Starts with two empty options
]);

on(['trip-updated' => '$refresh']);

$polls = computed(function () {
    return $this->trip->polls()->with('options', 'votes', 'creator')->get();
});

$addPollOptionField = function () {
    $this->poll_options[] = '';
};

$removePollOptionField = function ($index) {
    if (count($this->poll_options) > 2) {
        unset($this->poll_options[$index]);
        $this->poll_options = array_values($this->poll_options);
    }
};

$createPoll = function () {
    if (!$this->trip->canCreatePolls(auth()->user())) {
        abort(403);
    }

    $this->validate([
        'poll_title' => 'required|string|max:255',
        'poll_type' => 'required|string',
        'poll_options.*' => 'required|string|min:1',
    ]);

    $poll = Poll::create([
        'trip_id' => $this->trip->id,
        'title' => $this->poll_title,
        'description' => $this->poll_desc,
        'type' => $this->poll_type,
        'created_by' => auth()->id(),
    ]);

    foreach ($this->poll_options as $optionText) {
        PollOption::create([
            'poll_id' => $poll->id,
            'option_text' => $optionText,
        ]);
    }

    $this->reset(['poll_title', 'poll_desc', 'poll_type', 'poll_options', 'showPollModal']);
    $this->poll_options = ['', ''];
    $this->dispatch('trip-updated');
};

$castVote = function ($pollId, $optionId) {
    if (!$this->trip->canVote(auth()->user())) {
        abort(403);
    }

    $poll = Poll::findOrFail($pollId);
    if ($poll->is_locked) {
        return;
    }

    // Revoke previous vote in this poll if exists
    $existingVote = Vote::where('poll_id', $pollId)->where('user_id', auth()->id())->first();
    if ($existingVote) {
        $oldOption = PollOption::find($existingVote->poll_option_id);
        if ($oldOption && $oldOption->votes_count > 0) {
            $oldOption->decrement('votes_count');
        }
        $existingVote->delete();
    }

    // Cast new vote
    Vote::create([
        'poll_id' => $pollId,
        'poll_option_id' => $optionId,
        'user_id' => auth()->id(),
    ]);

    PollOption::find($optionId)->increment('votes_count');
    $this->dispatch('trip-updated');
};

$lockPoll = function ($pollId) {
    $poll = Poll::findOrFail($pollId);
    if (auth()->id() !== $poll->created_by && !$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    $poll->update(['is_locked' => !$poll->is_locked]);
    $this->dispatch('trip-updated');
};

?>

<div class="bg-bg-primary border border-border-light rounded-3xl p-5 shadow-sm"
     x-init="if(window.Motion) { window.Motion.animate($el, { y: [15, 0], opacity: [0, 1] }, { duration: 0.5, delay: 0.2 }) }">
    <div class="flex justify-between items-center border-b border-border-light pb-2.5 mb-4">
        <h3 class="font-extrabold text-sm text-brand-neutral font-sans-display flex items-center space-x-1.5">
            <i class="ph-bold ph-chats-teardrop text-base"></i>
            <span>Group Decisions</span>
        </h3>
        @if ($trip->canCreatePolls(auth()->user()))
            <button type="button" 
                    wire:click="$set('showPollModal', true)"
                    class="text-[10px] font-bold text-brand-neutral hover:text-brand-hover bg-brand-neutral/5 hover:bg-brand-neutral/10 px-2.5 py-1 rounded-lg transition focus:outline-none cursor-pointer">
                + Ask the Group
            </button>
        @endif
    </div>

    @php
        $unlockedPolls = $this->polls->where('is_locked', false);
    @endphp

    @if ($unlockedPolls->isEmpty())
        <div class="text-center py-6">
            <span class="text-2xl block mb-1">✨</span>
            <p class="text-xs text-text-muted font-medium">All decisions resolved!</p>
        </div>
    @else
        <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1">
            @foreach ($unlockedPolls as $poll)
                <div class="border border-border-light rounded-2xl p-4 bg-bg-secondary/30 relative">
                    <div class="flex justify-between items-start gap-2 mb-2">
                        <span class="text-[9px] font-bold tracking-wide text-brand-neutral bg-brand-neutral/5 px-2 py-0.5 rounded-full capitalize">
                            {{ $poll->type }}
                        </span>
                        @if (auth()->id() === $poll->created_by || $trip->canManageMembers(auth()->user()))
                            <button wire:click="lockPoll({{ $poll->id }})" 
                                    class="text-[9px] text-text-muted hover:text-text-main flex items-center space-x-1 border border-border-card bg-bg-primary px-1.5 py-0.5 rounded transition cursor-pointer">
                                <i class="ph ph-lock-key-open"></i>
                                <span>Lock</span>
                            </button>
                        @endif
                    </div>
                    <h4 class="font-bold text-xs text-text-main leading-snug">{{ $poll->title }}</h4>
                    @if($poll->description)
                        <p class="text-[10px] text-text-muted mt-1 leading-normal">{{ $poll->description }}</p>
                    @endif

                    <!-- Voting list -->
                    <div class="mt-3.5 space-y-2">
                        @php
                            $totalVotes = $poll->options->sum('votes_count');
                            $userVote = $poll->votes()->where('user_id', auth()->id())->first();
                        @endphp
                        @foreach ($poll->options as $opt)
                            @php
                                $percentage = $totalVotes > 0 ? ($opt->votes_count / $totalVotes) * 100 : 0;
                                $hasVotedForThis = $userVote && $userVote->poll_option_id == $opt->id;
                            @endphp
                            
                            <button type="button"
                                    wire:click="castVote({{ $poll->id }}, {{ $opt->id }})"
                                    @if(!$trip->canVote(auth()->user())) disabled @endif
                                    class="w-full text-left relative overflow-hidden border border-border-card bg-bg-primary hover:border-text-muted rounded-xl p-2.5 focus:outline-none transition cursor-pointer">
                                <!-- vote progress fill -->
                                <div class="absolute inset-0 bg-brand-neutral/5 z-0" style="width: {{ $percentage }}%"></div>
                                
                                <div class="flex justify-between items-center relative z-10 text-[11px] font-semibold">
                                    <span class="flex items-center space-x-1.5">
                                        @if ($hasVotedForThis)
                                            <i class="ph-bold ph-check text-emerald-600 text-xs"></i>
                                        @endif
                                        <span class="{{ $hasVotedForThis ? 'text-brand-neutral font-bold' : 'text-text-main' }}">{{ $opt->option_text }}</span>
                                    </span>
                                    <span class="text-[10px] text-text-muted">{{ $opt->votes_count }} {{ Str::plural('vote', $opt->votes_count) }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                    <div class="text-[9px] text-text-muted mt-3 pt-2.5 border-t border-border-light/70 flex justify-between">
                        <span>By: {{ $poll->creator->name }}</span>
                        <span>{{ $totalVotes }} total</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- POLL MODAL -->
    @if ($showPollModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
            <div class="bg-bg-primary border border-border-light w-full max-w-lg rounded-3xl shadow-xl p-6 md:p-8 animate-fade-in font-sans"
                 x-init="if(window.Motion) { window.Motion.animate($el, { scale: [0.95, 1], opacity: [0, 1] }, { duration: 0.25 }) }">
                <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                    <h2 class="text-lg font-bold text-brand-neutral font-sans-display flex items-center space-x-2">
                        <i class="ph ph-chats-teardrop text-lg"></i>
                        <span>Ask the group (New Poll)</span>
                    </h2>
                    <button type="button" wire:click="$set('showPollModal', false)" class="text-text-muted hover:text-text-main transition cursor-pointer p-1">
                        <i class="ph ph-x text-lg block"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createPoll" class="space-y-4">
                    <div>
                        <label for="poll_title" class="block text-xs font-bold text-text-main">Poll Question</label>
                        <input type="text" id="poll_title" wire:model="poll_title" placeholder="e.g. Which hotel should we book in Manali?"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        @error('poll_title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="poll_type" class="block text-xs font-bold text-text-main">Poll Category</label>
                            <select id="poll_type" wire:model="poll_type"
                                    class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                <option value="general">General Preference</option>
                                <option value="destination">Destination Selection</option>
                                <option value="hotel">Hotel Selection</option>
                                <option value="dates">Dates Selection</option>
                                <option value="activity">Activity Preference</option>
                            </select>
                        </div>

                        <div>
                            <label for="poll_desc" class="block text-xs font-bold text-text-main">Brief Details</label>
                            <input type="text" id="poll_desc" wire:model="poll_desc" placeholder="Helpful context..."
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        </div>
                    </div>

                    <!-- Dynamic Options Input List -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-text-main">Voting Options</label>
                        @foreach ($poll_options as $index => $option)
                            <div class="flex items-center space-x-2">
                                <input type="text" wire:model="poll_options.{{ $index }}" placeholder="Option {{ $index + 1 }}"
                                       class="flex-grow px-4 py-2 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                
                                @if (count($poll_options) > 2)
                                    <button type="button" wire:click="removePollOptionField({{ $index }})" 
                                            class="text-text-muted hover:text-red-600 transition p-1.5 cursor-pointer">
                                        <i class="ph ph-minus text-sm block"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        @error('poll_options.*') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror
                        
                        <button type="button" wire:click="addPollOptionField" 
                                class="text-xs font-bold text-brand-neutral hover:underline mt-1 block cursor-pointer">
                            + Add Option Field
                        </button>
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" wire:click="$set('showPollModal', false)" 
                                class="px-5 py-2.5 border border-border-card rounded-xl text-xs font-bold text-text-main hover:bg-bg-secondary transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl transition cursor-pointer">
                            Create Poll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
