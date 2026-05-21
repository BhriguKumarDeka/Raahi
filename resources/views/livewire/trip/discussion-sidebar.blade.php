<?php

use App\Models\Trip;
use App\Models\Comment;
use Illuminate\Support\Str;
use Carbon\Carbon;
use function Livewire\Volt\{state, computed, on};

state([
    'trip',
    'new_comment_content' => '',
    'replying_to_id' => null,
    'new_reply_content' => '',
]);

on(['trip-updated' => '$refresh']);

$comments = computed(function () {
    return $this->trip->comments()->whereNull('parent_id')->with('replies.user', 'user')->get();
});

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
            'action' => "shared a message in brainstorming",
            'time' => $comment->created_at ?: now(),
            'icon' => 'ph-chat-circle-text',
            'color' => 'text-rose-700 bg-rose-100/50 border border-rose-200/50',
        ]);
    }

    return $updates->sortByDesc('time')->take(5);
});

$addComment = function () {
    $this->validate([
        'new_comment_content' => 'required|string|max:1000',
    ]);

    Comment::create([
        'trip_id' => $this->trip->id,
        'user_id' => auth()->id(),
        'content' => $this->new_comment_content,
        'parent_id' => null,
    ]);

    $this->reset('new_comment_content');
    $this->dispatch('trip-updated');
};

$addReply = function ($parentId) {
    $this->validate([
        'new_reply_content' => 'required|string|max:1000',
    ]);

    Comment::create([
        'trip_id' => $this->trip->id,
        'user_id' => auth()->id(),
        'content' => $this->new_reply_content,
        'parent_id' => $parentId,
    ]);

    $this->reset(['replying_to_id', 'new_reply_content']);
    $this->dispatch('trip-updated');
};

$deleteComment = function ($commentId) {
    $comment = Comment::findOrFail($commentId);
    if (auth()->id() !== $comment->user_id && !$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    $comment->delete();
    $this->dispatch('trip-updated');
};

?>

<div class="space-y-6">
    <!-- 2. BRAINSTORMING CHAT FEED -->
    <div class="bg-bg-primary border border-border-light rounded-3xl p-5 shadow-sm flex flex-col max-h-[480px]"
         x-init="if(window.Motion) { window.Motion.animate($el, { y: [15, 0], opacity: [0, 1] }, { duration: 0.5, delay: 0.3 }) }">
        <div class="border-b border-border-light pb-2.5 mb-3">
            <h3 class="font-extrabold text-sm text-brand-neutral font-sans-display flex items-center space-x-1.5">
                <i class="ph-bold ph-chats-circle text-base"></i>
                <span>Brainstorming Feed</span>
            </h3>
        </div>

        <!-- Comments and thread list scrollable -->
        <div class="flex-grow overflow-y-auto space-y-3.5 pr-1 mb-3 text-xs">
            @if ($this->comments->isEmpty())
                <p class="text-center text-text-muted py-6">No messages posted. Start the brainstorming!</p>
            @else
                @foreach ($this->comments as $comment)
                    <div class="space-y-1">
                        <div class="flex justify-between items-start gap-1">
                            <div class="flex items-center space-x-2">
                                <div class="h-6 w-6 rounded-full bg-brand-neutral text-bg-primary flex items-center justify-center font-bold text-[9px] uppercase">
                                    {{ $comment->user->name[0] }}
                                </div>
                                <div>
                                    <h5 class="font-bold text-[11px] text-text-main">{{ $comment->user->name }}</h5>
                                    <span class="text-[8px] text-text-muted block">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex space-x-1">
                                <button wire:click="$set('replying_to_id', {{ $comment->id }})" 
                                        class="text-[9px] text-brand-neutral hover:underline focus:outline-none transition cursor-pointer">
                                    Reply
                                </button>
                                @if (auth()->id() === $comment->user_id || $trip->canManageMembers(auth()->user()))
                                    <button wire:click="deleteComment({{ $comment->id }})" 
                                            class="text-text-muted hover:text-red-600 transition p-0.5 cursor-pointer">
                                        <i class="ph ph-trash text-[10px]"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <p class="text-text-main text-xs pl-8 leading-relaxed">{{ $comment->content }}</p>

                        <!-- Replies listing -->
                        @if ($comment->replies->isNotEmpty())
                            <div class="pl-8 border-l border-l-border-light space-y-2 mt-2 ml-3">
                                @foreach ($comment->replies as $reply)
                                    <div class="space-y-0.5">
                                        <div class="flex justify-between items-start gap-1">
                                            <div class="flex items-center space-x-1.5">
                                                <div class="h-5 w-5 rounded-full bg-bg-secondary text-text-main border border-border-card flex items-center justify-center font-bold text-[8px] uppercase">
                                                    {{ $reply->user->name[0] }}
                                                </div>
                                                <div>
                                                    <h6 class="font-bold text-[10px] text-text-main leading-none">{{ $reply->user->name }}</h6>
                                                    <span class="text-[8px] text-text-muted block mt-0.5">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            @if (auth()->id() === $reply->user_id || $trip->canManageMembers(auth()->user()))
                                                <button wire:click="deleteComment({{ $reply->id }})" 
                                                        class="text-text-muted hover:text-red-600 transition p-0.5 cursor-pointer">
                                                    <i class="ph ph-trash text-[9px]"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="text-text-main text-[11px] pl-6 leading-relaxed">{{ $reply->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Inline Reply textbox -->
                        @if ($replying_to_id === $comment->id)
                            <div class="pl-8 mt-2 border-l-2 border-l-brand-neutral ml-3">
                                <form wire:submit.prevent="addReply({{ $comment->id }})" class="space-y-1.5">
                                    <input type="text" wire:model="new_reply_content" placeholder="Write a reply..."
                                           class="block w-full px-2.5 py-1.5 border border-border-card rounded-lg text-[10px] bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                    <div class="flex justify-end space-x-1.5">
                                        <button type="button" wire:click="$set('replying_to_id', null)" 
                                                class="px-2 py-1 border border-border-card rounded-md text-[9px] hover:bg-bg-secondary transition cursor-pointer">
                                            Cancel
                                        </button>
                                        <button type="submit" 
                                                class="px-2 py-1 bg-brand-neutral text-bg-primary text-[9px] font-bold rounded-md transition cursor-pointer">
                                            Reply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Submit Comment form -->
        <form wire:submit.prevent="addComment" class="border-t border-border-light pt-2.5 mt-auto">
            <div class="relative flex items-center">
                <textarea wire:model="new_comment_content" rows="1" placeholder="Send to brainstorming feed..."
                          class="block w-full pr-10 pl-3 py-2 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral resize-none"></textarea>
                <button type="submit" 
                        class="absolute right-2 text-brand-neutral hover:text-brand-hover p-1 transition cursor-pointer">
                    <i class="ph-bold ph-paper-plane-right text-base block"></i>
                </button>
            </div>
            @error('new_comment_content') <span class="text-[10px] text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </form>
    </div>

    <!-- 3. WORKSPACE ACTIVITY (RECENT UPDATES) -->
    <div class="bg-bg-primary border border-border-light rounded-3xl p-5 shadow-sm"
         x-init="if(window.Motion) { window.Motion.animate($el, { y: [15, 0], opacity: [0, 1] }, { duration: 0.5, delay: 0.4 }) }">
        <div class="border-b border-border-light pb-2.5 mb-4">
            <h3 class="font-extrabold text-sm text-brand-neutral font-sans-display flex items-center space-x-1.5">
                <i class="ph-bold ph-activity text-base"></i>
                <span>Recent Updates</span>
            </h3>
        </div>

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
    </div>
</div>
