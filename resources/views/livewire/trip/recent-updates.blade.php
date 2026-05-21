<?php

use App\Models\Trip;
use Carbon\Carbon;
use function Livewire\Volt\{state, computed, on};

state([
    'trip',
]);

on(['trip-updated' => '$refresh']);

$recentUpdates = computed(function () {
    $updates = collect();
    $trip = $this->trip;

    // Load related items to avoid N+1 queries
    $itinerary = $trip->itineraryItems()->with('creator')->get();
    $expenses = $trip->expenses()->with('payer')->get();
    $polls = $trip->polls()->with('creator')->get();
    $documents = $trip->documents()->with('uploader')->get();
    $comments = $trip->comments()->with('user')->get();

    foreach ($itinerary as $item) {
        $updates->push([
            'user' => $item->creator ? $item->creator->name : 'Someone',
            'action' => "planned a new event \"{$item->title}\"",
            'time' => $item->created_at ?: now(),
            'icon' => 'ph-calendar-check',
            'color' => 'text-emerald-700 bg-emerald-100/50 border border-emerald-200/50',
        ]);
    }

    foreach ($expenses as $expense) {
        $updates->push([
            'user' => $expense->payer ? $expense->payer->name : 'Someone',
            'action' => "logged a shared bill \"{$expense->title}\" of ₹" . number_format($expense->amount, 0),
            'time' => $expense->created_at ?: ($expense->date ? \Carbon\Carbon::parse($expense->date) : now()),
            'icon' => 'ph-receipt',
            'color' => 'text-amber-700 bg-amber-100/50 border border-amber-200/50',
        ]);
    }

    foreach ($polls as $poll) {
        $updates->push([
            'user' => $poll->creator ? $poll->creator->name : 'Someone',
            'action' => "asked the group \"{$poll->title}\"",
            'time' => $poll->created_at ?: now(),
            'icon' => 'ph-question',
            'color' => 'text-indigo-700 bg-indigo-100/50 border border-indigo-200/50',
        ]);
    }

    foreach ($documents as $doc) {
        $updates->push([
            'user' => $doc->uploader ? $doc->uploader->name : 'Someone',
            'action' => "uploaded a document \"{$doc->name}\"",
            'time' => $doc->created_at ?: now(),
            'icon' => 'ph-file-arrow-up',
            'color' => 'text-sky-700 bg-sky-100/50 border border-sky-200/50',
        ]);
    }

    foreach ($comments as $comment) {
        $updates->push([
            'user' => $comment->user ? $comment->user->name : 'Someone',
            'action' => "shared a message in group chat",
            'time' => $comment->created_at ?: now(),
            'icon' => 'ph-chat-circle-text',
            'color' => 'text-rose-700 bg-rose-100/50 border border-rose-200/50',
        ]);
    }

    return $updates->sortByDesc('time')->take(15);
});

?>

<div class="space-y-4"
     x-init="if(window.Motion) { window.Motion.animate($el, { y: [10, 0], opacity: [0, 1] }, { duration: 0.4 }) }">

    {{-- Header --}}
    <div class="border-b border-border-light pb-2.5 mb-4">
        <h3 class="font-extrabold text-sm text-brand-neutral font-sans-display flex items-center space-x-1.5">
            <i class="ph-bold ph-activity text-base"></i>
            <span>Recent Updates</span>
        </h3>
    </div>

    {{-- Activity Timeline --}}
    @if ($this->recentUpdates->isEmpty())
        <div class="text-center py-8">
            <i class="ph ph-activity text-3xl text-text-muted/40 block mb-2"></i>
            <p class="text-xs text-text-muted">No activity yet. Updates will appear here as your trip takes shape.</p>
        </div>
    @else
        <div class="space-y-4 relative before:absolute before:inset-y-0 before:left-3 before:w-px before:bg-border-light">
            @foreach ($this->recentUpdates as $update)
                <div class="flex items-start space-x-3.5 relative">
                    <div class="h-6.5 w-6.5 rounded-full shrink-0 flex items-center justify-center text-xs relative z-10 font-bold border border-bg-primary {{ $update['color'] }}">
                        <i class="ph {{ $update['icon'] }}"></i>
                    </div>
                    <div class="text-[11px] leading-relaxed">
                        <p class="text-text-main"><strong class="font-bold">{{ $update['user'] }}</strong> {{ $update['action'] }}.</p>
                        <span class="text-[9px] text-text-muted block mt-0.5">{{ $update['time']->diffForHumans() }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
