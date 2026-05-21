<?php

use function Livewire\Volt\{state, rules, computed, uses, mount};
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Trip;
use App\Models\ItineraryItem;
use App\Models\Expense;
use App\Models\ExpenseUser;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\Document;
use App\Models\Invitation;
use App\Models\User;

uses([WithFileUploads::class]);

state([
    'trip',
    'activeTab' => 'overview',
    
    // Itinerary form fields
    'showItineraryModal' => false,
    'itinerary_title' => '',
    'itinerary_desc' => '',
    'itinerary_datetime' => '',
    'itinerary_location' => '',
    'itinerary_duration' => 60,
    'itinerary_cost' => 0,
    'itinerary_category' => 'activity',

    // Budget form fields
    'showExpenseModal' => false,
    'expense_title' => '',
    'expense_amount' => '',
    'expense_paid_by' => '',
    'expense_category' => 'miscellaneous',
    'expense_date' => '',
    'selected_members' => [], // who shares the cost

    // Poll form fields
    'showPollModal' => false,
    'poll_title' => '',
    'poll_desc' => '',
    'poll_type' => 'general',
    'poll_options' => ['', ''], // Starts with two empty options
    
    // Member form fields
    'invite_email' => '',
    'invite_role' => 'member',

    // Document form fields
    'document_file' => null,

    // Discussion form fields
    'new_comment_content' => '',
    'replying_to_id' => null,
    'new_reply_content' => '',
]);

// Set default state values on mount
mount(function (Trip $trip) {
    $this->trip = $trip;
    $this->expense_paid_by = auth()->id();
    $this->expense_date = date('Y-m-d');
    $this->selected_members = $trip->users->pluck('id')->toArray();
});

// Computed Properties
$users = computed(function () {
    return $this->trip->users;
});

$itineraryItems = computed(function () {
    return $this->trip->itineraryItems;
});

$expenses = computed(function () {
    return $this->trip->expenses;
});

$polls = computed(function () {
    return $this->trip->polls;
});

$documents = computed(function () {
    return $this->trip->documents;
});

$comments = computed(function () {
    return $this->trip->comments()->whereNull('parent_id')->with('replies.user', 'user')->get();
});

// Settlement net calculation
$settlements = computed(function () {
    $members = $this->trip->users;
    $netBalances = [];
    
    foreach ($members as $member) {
        $netBalances[$member->id] = [
            'user' => $member,
            'paid' => 0,
            'owed' => 0,
            'balance' => 0
        ];
    }

    $tripExpenses = $this->trip->expenses()->with('splits')->get();

    foreach ($tripExpenses as $expense) {
        $payerId = $expense->paid_by;
        if (isset($netBalances[$payerId])) {
            $netBalances[$payerId]['paid'] += $expense->amount;
        }

        foreach ($expense->splits as $split) {
            $debtorId = $split->user_id;
            if (isset($netBalances[$debtorId])) {
                $netBalances[$debtorId]['owed'] += $split->share;
            }
        }
    }

    foreach ($netBalances as $id => $data) {
        $netBalances[$id]['balance'] = $data['paid'] - $data['owed'];
    }

    // Settlement algorithm (Greedy Creditor-Debtor matching)
    $debtors = [];
    $creditors = [];

    foreach ($netBalances as $id => $data) {
        $balance = round($data['balance'], 2);
        if ($balance < 0) {
            $debtors[] = ['id' => $id, 'user' => $data['user'], 'amount' => abs($balance)];
        } elseif ($balance > 0) {
            $creditors[] = ['id' => $id, 'user' => $data['user'], 'amount' => $balance];
        }
    }

    $instructions = [];
    $i = 0; $j = 0;

    while ($i < count($debtors) && $j < count($creditors)) {
        $debtor = &$debtors[$i];
        $creditor = &$creditors[$j];

        $payment = min($debtor['amount'], $creditor['amount']);
        if ($payment > 0.01) {
            $instructions[] = [
                'from' => $debtor['user'],
                'to' => $creditor['user'],
                'amount' => $payment
            ];
        }

        $debtor['amount'] -= $payment;
        $creditor['amount'] -= $payment;

        if ($debtor['amount'] < 0.01) {
            $i++;
        }
        if ($creditor['amount'] < 0.01) {
            $j++;
        }
    }

    return [
        'balances' => $netBalances,
        'instructions' => $instructions
    ];
});

// Operations: Itinerary
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
};

$deleteItineraryItem = function ($id) {
    if (!$this->trip->canEditItinerary(auth()->user())) {
        abort(403);
    }

    ItineraryItem::destroy($id);
};

// Operations: Expense
$addExpense = function () {
    if (!$this->trip->canManageExpenses(auth()->user())) {
        abort(403);
    }

    $this->validate([
        'expense_title' => 'required|string|max:255',
        'expense_amount' => 'required|numeric|min:0.01',
        'expense_paid_by' => 'required|exists:users,id',
        'expense_category' => 'required|string',
        'expense_date' => 'required|date',
        'selected_members' => 'required|array|min:1',
    ]);

    $expense = Expense::create([
        'trip_id' => $this->trip->id,
        'title' => $this->expense_title,
        'amount' => $this->expense_amount,
        'paid_by' => $this->expense_paid_by,
        'split_type' => 'equal',
        'category' => $this->expense_category,
        'date' => $this->expense_date,
    ]);

    // Equal split math
    $splitCount = count($this->selected_members);
    $shareAmount = round($this->expense_amount / $splitCount, 2);

    foreach ($this->selected_members as $memberId) {
        ExpenseUser::create([
            'expense_id' => $expense->id,
            'user_id' => $memberId,
            'share' => $shareAmount,
            'is_paid' => $memberId == $this->expense_paid_by,
        ]);
    }

    $this->reset(['expense_title', 'expense_amount', 'showExpenseModal']);
    $this->selected_members = $this->trip->users->pluck('id')->toArray();
};

$deleteExpense = function ($id) {
    if (!$this->trip->canManageExpenses(auth()->user())) {
        abort(403);
    }

    Expense::destroy($id);
};

// Operations: Polls
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
};

$lockPoll = function ($pollId) {
    $poll = Poll::findOrFail($pollId);
    if (auth()->id() !== $poll->created_by && !$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    $poll->update(['is_locked' => !$poll->is_locked]);
};

// Operations: Members & Invitations
$sendInvitation = function () {
    if (!$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    $this->validate([
        'invite_email' => 'required|email',
        'invite_role' => 'required|string',
    ]);

    // Generate unique token
    $token = bin2hex(random_bytes(32));

    Invitation::create([
        'trip_id' => $this->trip->id,
        'email' => $this->invite_email,
        'token' => $token,
        'role' => $this->invite_role,
        'invited_by' => auth()->id(),
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    // In a production setup, we would mail this link: Route::route('invitations.accept', $token)
    // To make it easy for testing, we will output a session status
    session()->flash('invitation_link', route('invitations.accept', $token));
    
    $this->reset(['invite_email', 'invite_role']);
};

$updateUserRole = function ($userId, $newRole) {
    if (!$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    if ($userId == auth()->id()) {
        // Can't demote yourself
        return;
    }

    $this->trip->users()->updateExistingPivot($userId, ['role' => $newRole]);
};

$removeUser = function ($userId) {
    if (!$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    if ($userId == auth()->id()) {
        return;
    }

    $this->trip->users()->detach($userId);
};

// Operations: Documents
$uploadDocument = function () {
    $this->validate([
        'document_file' => 'required|file|max:10240', // Max 10MB
    ]);

    $originalName = $this->document_file->getClientOriginalName();
    $extension = $this->document_file->getClientOriginalExtension();
    $size = $this->document_file->getSize();

    $path = $this->document_file->store('documents', 'local');

    Document::create([
        'trip_id' => $this->trip->id,
        'uploaded_by' => auth()->id(),
        'name' => $originalName,
        'file_path' => $path,
        'file_size' => $size,
        'file_type' => $extension,
    ]);

    $this->reset('document_file');
};

$downloadDocument = function ($docId) {
    $document = Document::findOrFail($docId);
    if (!$this->trip->canView(auth()->user())) {
        abort(403);
    }

    return Storage::disk('local')->download($document->file_path, $document->name);
};

$deleteDocument = function ($docId) {
    $document = Document::findOrFail($docId);
    if (auth()->id() !== $document->uploaded_by && !$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    Storage::disk('local')->delete($document->file_path);
    $document->delete();
};

// Operations: Discussion
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
};

$deleteComment = function ($commentId) {
    $comment = Comment::findOrFail($commentId);
    if (auth()->id() !== $comment->user_id && !$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    $comment->delete();
};

?>

<div class="py-8 bg-bg-secondary min-h-screen font-sans text-text-main">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Trip Header Banner -->
        <div class="bg-bg-primary border border-border-light rounded-2xl p-8 mb-8 shadow-sm relative overflow-hidden">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
                <div>
                    <span class="text-xs text-text-muted font-bold uppercase tracking-wider">{{ $trip->destination }}</span>
                    <h1 class="text-3xl font-extrabold tracking-tight mt-1 font-display">{{ $trip->name }}</h1>
                    <p class="text-text-muted text-sm mt-2 leading-relaxed max-w-2xl">{{ $trip->description ?: 'No description provided.' }}</p>
                </div>
                <div class="bg-bg-secondary border border-border-light px-4 py-3 rounded-xl text-center shadow-none min-w-[150px]">
                    <span class="text-xs text-text-muted uppercase block">Countdown</span>
                    @php
                        $daysLeft = now()->startOfDay()->diffInDays($trip->start_date, false);
                    @endphp
                    <span class="text-xl font-extrabold tracking-tight">
                        @if ($daysLeft > 0)
                            {{ $daysLeft }} Days Left
                        @elseif ($daysLeft === 0)
                            Today!
                        @else
                            Completed
                        @endif
                    </span>
                </div>
            </div>
            
            <!-- Dates and details bar -->
            <div class="flex flex-wrap items-center mt-6 pt-6 border-t border-border-light text-sm text-text-muted gap-6 relative z-10">
                <div class="flex items-center space-x-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $trip->start_date->format('M d, Y') }} - {{ $trip->end_date->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V5" />
                    </svg>
                    <span>Budget: <strong>${{ number_format($trip->budget_estimate, 2) }}</strong></span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ $this->users->count() }} Members</span>
                </div>
            </div>
        </div>

        <!-- Trip Workspace Tabs -->
        <div class="border-b border-border-light mb-8">
            <nav class="flex space-x-6 overflow-x-auto pb-px" aria-label="Tabs">
                @foreach ([
                    'overview' => 'Overview',
                    'itinerary' => 'Itinerary',
                    'budget' => 'Budget',
                    'polls' => 'Polls',
                    'members' => 'Members',
                    'documents' => 'Documents',
                    'discussion' => 'Discussion'
                ] as $tab => $label)
                    <button type="button" 
                            wire:click="$set('activeTab', '{{ $tab }}')"
                            class="py-4 border-b-2 font-medium text-sm whitespace-nowrap focus:outline-none transition duration-150 {{ $activeTab === $tab ? 'border-brand-neutral text-text-main font-semibold' : 'border-transparent text-text-muted hover:text-text-main hover:border-border-card' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Views -->
        <div class="transition-all duration-200">
            
            <!-- OVERVIEW TAB -->
            @if ($activeTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left: Itinerary summary/next activity & Expenses summary -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Next Activity -->
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                            <h2 class="font-bold text-lg mb-4">Upcoming Itinerary</h2>
                            @php
                                $nextItem = $this->itineraryItems->where('datetime', '>=', now())->first();
                            @endphp
                            @if ($nextItem)
                                <div class="p-4 bg-bg-secondary border border-border-light rounded-xl flex justify-between items-center">
                                    <div>
                                        <span class="text-xs font-semibold text-text-muted uppercase tracking-wider block">{{ $nextItem->category }}</span>
                                        <h3 class="font-bold text-base mt-0.5">{{ $nextItem->title }}</h3>
                                        <p class="text-xs text-text-muted mt-1">{{ $nextItem->datetime->format('M d, Y @ h:i A') }}</p>
                                    </div>
                                    @if ($nextItem->cost > 0)
                                        <span class="font-bold text-sm">${{ number_format($nextItem->cost, 2) }}</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-text-muted">No upcoming activities scheduled.</p>
                            @endif
                        </div>

                        <!-- General description and trip highlights -->
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm space-y-4">
                            <h2 class="font-bold text-lg">Trip Notes</h2>
                            <p class="text-sm text-text-muted leading-relaxed">
                                Welcome to your group travel space! Use the tabs above to collaborate.
                                <br><br>
                                - <strong>Itinerary</strong>: Add flights, hotels, and tourist attractions day-by-day.
                                <br>
                                - <strong>Budget</strong>: Keep track of bills, group dinners, and split payments fairly.
                                <br>
                                - <strong>Polls</strong>: Vote on hotels or dates when preferences conflict.
                                <br>
                                - <strong>Discussion</strong>: Chat with friends directly in the context of this trip.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Quick stats & Members panel -->
                    <div class="space-y-6">
                        <!-- Quick Stats card -->
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm space-y-4">
                            <h2 class="font-bold text-lg">Expense Summary</h2>
                            @php
                                $totalPaid = $this->expenses->sum('amount');
                                $percentOfBudget = $trip->budget_estimate > 0 ? min(($totalPaid / $trip->budget_estimate) * 100, 100) : 0;
                            @endphp
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-text-muted">Total Spent:</span>
                                    <span class="font-bold">${{ number_format($totalPaid, 2) }}</span>
                                </div>
                                <div class="w-full bg-border-light h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-neutral h-full" style="width: {{ $percentOfBudget }}%"></div>
                                </div>
                                <div class="text-[10px] text-text-muted flex justify-between">
                                    <span>Spent: ${{ number_format($totalPaid, 0) }}</span>
                                    <span>Limit: ${{ number_format($trip->budget_estimate, 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Active Members List -->
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                            <h2 class="font-bold text-lg mb-4">Planning Team</h2>
                            <div class="divide-y divide-border-light">
                                @foreach ($this->users as $u)
                                    <div class="flex items-center justify-between py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-bg-secondary border border-border-light flex items-center justify-center font-bold text-xs uppercase text-text-main">
                                                {{ $u->name[0] }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-text-main">{{ $u->name }}</p>
                                                <p class="text-xs text-text-muted capitalize">{{ $trip->getUserRole($u) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ITINERARY TAB -->
            @if ($activeTab === 'itinerary')
                <div class="space-y-6">
                    <!-- Action Bar -->
                    @if ($trip->canEditItinerary(auth()->user()))
                        <div class="flex justify-end">
                            <button type="button" 
                                    wire:click="$set('showItineraryModal', true)"
                                    class="px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-xs rounded-xl shadow-none focus:outline-none transition">
                                + Add Activity
                            </button>
                        </div>
                    @endif

                    @if ($this->itineraryItems->isEmpty())
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-12 text-center shadow-sm">
                            <h3 class="text-lg font-bold">No activities scheduled</h3>
                            <p class="text-sm text-text-muted mt-1">Start detailing flights, accommodations, and sightseeing tours!</p>
                        </div>
                    @else
                        <!-- Day-by-Day Grouped Timeline -->
                        @php
                            $groupedItems = $this->itineraryItems->groupBy(function($item) {
                                return $item->datetime->format('Y-m-d');
                            });
                        @endphp
                        
                        <div class="space-y-8">
                            @foreach ($groupedItems as $dateStr => $dayItems)
                                <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                    <h3 class="text-lg font-bold border-b border-border-light pb-3 mb-4">
                                        {{ \Carbon\Carbon::parse($dateStr)->format('l, M d, Y') }}
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        @foreach ($dayItems as $item)
                                            <div class="p-4 bg-bg-secondary border border-border-light rounded-xl hover:shadow-sm transition flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                                <div class="flex items-start space-x-3">
                                                    <!-- Icon mapping -->
                                                    <span class="p-2 bg-bg-primary border border-border-light rounded-lg text-text-main flex items-center justify-center">
                                                        @if ($item->category === 'transport')
                                                            ✈️
                                                        @elseif ($item->category === 'accommodation')
                                                            🏨
                                                        @elseif ($item->category === 'food')
                                                            🍽️
                                                        @else
                                                            📍
                                                        @endif
                                                    </span>
                                                    <div>
                                                        <div class="flex items-center space-x-2">
                                                            <h4 class="font-bold text-text-main">{{ $item->title }}</h4>
                                                            <span class="px-2 py-0.5 border border-border-card bg-bg-primary rounded text-[10px] font-semibold text-text-muted capitalize">{{ $item->category }}</span>
                                                        </div>
                                                        <p class="text-xs text-text-muted mt-1">
                                                            {{ $item->datetime->format('h:i A') }} 
                                                            @if($item->duration_minutes) ({{ $item->duration_minutes }} mins) @endif
                                                            @if($item->location) &bull; {{ $item->location }} @endif
                                                        </p>
                                                        @if($item->description)
                                                            <p class="text-xs text-text-muted mt-2">{{ $item->description }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-4 self-end md:self-auto">
                                                    @if ($item->cost > 0)
                                                        <span class="font-bold text-sm">${{ number_format($item->cost, 2) }}</span>
                                                    @endif
                                                    
                                                    @if ($trip->canEditItinerary(auth()->user()))
                                                        <button wire:click="deleteItineraryItem({{ $item->id }})" 
                                                                wire:confirm="Are you sure you want to remove this activity?"
                                                                class="text-text-muted hover:text-red-600 transition p-1">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
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
                </div>
            @endif

            <!-- BUDGET TAB -->
            @if ($activeTab === 'budget')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left: Settlement & Debts sheet -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Net Balances List -->
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-base mb-4">Balances</h3>
                                <div class="space-y-3">
                                    @foreach ($this->settlements['balances'] as $memberId => $balanceData)
                                        <div class="flex justify-between items-center text-sm py-1 border-b border-border-light">
                                            <span class="font-semibold">{{ $balanceData['user']->name }}</span>
                                            @php
                                                $bal = round($balanceData['balance'], 2);
                                            @endphp
                                            <span class="font-bold {{ $bal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $bal >= 0 ? '+' : '' }}${{ number_format($bal, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Net settlements recommendations -->
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-base mb-4">Net Payments</h3>
                                @if (empty($this->settlements['instructions']))
                                    <p class="text-xs text-text-muted">All balances are settled!</p>
                                @else
                                    <div class="space-y-3">
                                        @foreach ($this->settlements['instructions'] as $inst)
                                            <div class="text-sm p-3 bg-bg-secondary border border-border-light rounded-xl">
                                                <strong>{{ $inst['from']->name }}</strong> owes 
                                                <strong>{{ $inst['to']->name }}</strong> 
                                                <span class="font-bold text-green-600">${{ number_format($inst['amount'], 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Category Breakdown Chart -->
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-base mb-4">Category Breakdown</h3>
                                @php
                                    $categoryTotals = [];
                                    $totalAmount = 0;
                                    foreach ($this->expenses as $exp) {
                                        $cat = strtolower($exp->category ?: 'other');
                                        $categoryTotals[$cat] = ($categoryTotals[$cat] ?? 0) + $exp->amount;
                                        $totalAmount += $exp->amount;
                                    }
                                    
                                    $icons = [
                                        'transport' => '🚗',
                                        'accommodation' => '🏨',
                                        'food' => '🍔',
                                        'activities' => '🎟️',
                                        'other' => '📦'
                                    ];
                                @endphp
                                @if ($totalAmount == 0)
                                    <p class="text-xs text-text-muted">No expenses recorded yet to show breakdown.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach (['transport', 'accommodation', 'food', 'activities', 'other'] as $cat)
                                            @php
                                                $amt = $categoryTotals[$cat] ?? 0;
                                                $pct = $totalAmount > 0 ? ($amt / $totalAmount) * 100 : 0;
                                                $emoji = $icons[$cat] ?? '📦';
                                            @endphp
                                            @if ($amt > 0)
                                                <div class="space-y-1">
                                                    <div class="flex justify-between text-xs font-semibold">
                                                        <span>{{ $emoji }} <span class="capitalize ml-1">{{ $cat }}</span></span>
                                                        <span class="text-text-muted">${{ number_format($amt, 2) }} ({{ round($pct) }}%)</span>
                                                    </div>
                                                    <div class="w-full bg-bg-secondary h-2 rounded-full overflow-hidden border border-border-light">
                                                        <div class="bg-brand-neutral h-full rounded-full" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Expenses List -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Action Header -->
                            <div class="flex justify-between items-center">
                                <h3 class="font-bold text-lg">Trip Expenses</h3>
                                @if ($trip->canManageExpenses(auth()->user()))
                                    <button type="button" 
                                            wire:click="$set('showExpenseModal', true)"
                                            class="px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-xs rounded-xl shadow-none focus:outline-none transition">
                                        + Record Expense
                                    </button>
                                @endif
                            </div>

                            @if ($this->expenses->isEmpty())
                                <div class="bg-bg-primary border border-border-light rounded-2xl p-12 text-center shadow-sm">
                                    <h4 class="text-sm font-bold text-text-main">No expenses recorded yet.</h4>
                                </div>
                            @else
                                <div class="bg-bg-primary border border-border-light rounded-2xl overflow-hidden shadow-sm">
                                    <div class="divide-y divide-border-light">
                                        @foreach ($this->expenses as $exp)
                                            <div class="p-6 flex justify-between items-center hover:bg-bg-secondary transition">
                                                <div>
                                                    <h4 class="font-bold text-base text-text-main">{{ $exp->title }}</h4>
                                                    <p class="text-xs text-text-muted mt-1">
                                                        Paid by <strong class="text-text-main">{{ $exp->payer->name }}</strong> on {{ $exp->date->format('M d, Y') }} 
                                                        &bull; Category: <span class="capitalize">{{ $exp->category }}</span>
                                                    </p>
                                                    
                                                    <!-- Splits list -->
                                                    <div class="flex flex-wrap gap-2 mt-3">
                                                        @foreach($exp->splits as $split)
                                                            <span class="bg-bg-primary border border-border-light text-[10px] px-2 py-0.5 rounded-full text-text-muted">
                                                                {{ $split->user->name }}: ${{ number_format($split->share, 2) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-4">
                                                    <span class="font-bold text-lg">${{ number_format($exp->amount, 2) }}</span>
                                                    
                                                    @if ($trip->canManageExpenses(auth()->user()))
                                                        <button wire:click="deleteExpense({{ $exp->id }})" 
                                                                wire:confirm="Are you sure you want to delete this expense?"
                                                                class="text-text-muted hover:text-red-600 transition p-1">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- POLLS TAB -->
            @if ($activeTab === 'polls')
                <div class="space-y-6" wire:poll.5s>
                    <!-- Create Poll Bar -->
                    @if ($trip->canCreatePolls(auth()->user()))
                        <div class="flex justify-end">
                            <button type="button" 
                                    wire:click="$set('showPollModal', true)"
                                    class="px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-xs rounded-xl shadow-none focus:outline-none transition">
                                + Create Poll
                            </button>
                        </div>
                    @endif

                    @if ($this->polls->isEmpty())
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-12 text-center shadow-sm">
                            <h3 class="text-lg font-bold">No active polls</h3>
                            <p class="text-sm text-text-muted mt-1">Create polls to vote on hotels, dates, or activities.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($this->polls as $poll)
                                <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[10px] border border-border-card bg-bg-secondary px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-text-muted capitalize">
                                                {{ $poll->type }}
                                            </span>
                                            
                                            <!-- Lock Poll Action -->
                                            @if (auth()->id() === $poll->created_by || $trip->canManageMembers(auth()->user()))
                                                <button wire:click="lockPoll({{ $poll->id }})" 
                                                        class="text-xs text-text-muted hover:text-text-main flex items-center space-x-1 border border-border-card px-2 py-1 rounded hover:bg-bg-secondary transition">
                                                    @if($poll->is_locked)
                                                        🔒 Locked
                                                    @else
                                                        🔓 Lock
                                                    @endif
                                                </button>
                                            @elseif ($poll->is_locked)
                                                <span class="text-xs text-text-muted">🔒 Locked</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-lg text-text-main">{{ $poll->title }}</h3>
                                        @if($poll->description)
                                            <p class="text-xs text-text-muted mt-1 leading-relaxed">{{ $poll->description }}</p>
                                        @endif
                                        
                                        <!-- Poll Options & Voting -->
                                        <div class="mt-6 space-y-3">
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
                                                        @if($poll->is_locked || !$trip->canVote(auth()->user())) disabled @endif
                                                        class="w-full text-left relative overflow-hidden border border-border-card rounded-xl p-3 focus:outline-none transition {{ $hasVotedForThis ? 'border-brand-neutral' : 'hover:border-text-muted' }}">
                                                    <!-- Percentage progress fill -->
                                                    <div class="absolute inset-0 bg-bg-secondary z-0" style="width: {{ $percentage }}%"></div>
                                                    
                                                    <div class="flex justify-between items-center relative z-10 text-sm">
                                                        <span class="font-medium {{ $hasVotedForThis ? 'font-bold' : '' }}">{{ $opt->option_text }}</span>
                                                        <span class="text-xs font-bold">{{ $opt->votes_count }} {{ Str::plural('vote', $opt->votes_count) }}</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-text-muted mt-6 pt-4 border-t border-border-light flex justify-between">
                                        <span>Created by: {{ $poll->creator->name }}</span>
                                        <span>Total: {{ $totalVotes }} votes</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- MEMBERS TAB -->
            @if ($activeTab === 'members')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left: Invite Member -->
                        <div class="lg:col-span-1">
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-lg mb-4">Invite Friends</h3>
                                
                                @if (session('invitation_link'))
                                    <div class="mb-4 p-3 bg-bg-secondary border border-border-card rounded-xl text-xs space-y-2">
                                        <p class="font-bold">Share this acceptance link:</p>
                                        <input type="text" readonly value="{{ session('invitation_link') }}" 
                                               class="w-full bg-bg-primary border border-border-card rounded p-1.5 font-mono text-[10px] text-text-main focus:outline-none">
                                        <p class="text-[10px] text-text-muted">In a real app, this link would be emailed automatically.</p>
                                    </div>
                                @endif

                                @if ($trip->canManageMembers(auth()->user()))
                                    <form wire:submit.prevent="sendInvitation" class="space-y-4">
                                        <div>
                                            <label for="invite_email" class="block text-sm font-semibold text-text-main">Email Address</label>
                                            <input type="email" id="invite_email" wire:model="invite_email" placeholder="friend@example.com"
                                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                            @error('invite_email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="invite_role" class="block text-sm font-semibold text-text-main">Access Role</label>
                                            <select id="invite_role" wire:model="invite_role"
                                                    class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                                <option value="co_planner">Co-Planner (Can Edit Itinerary)</option>
                                                <option value="member">Member (Can Comment & Vote)</option>
                                                <option value="viewer">Viewer (Read Only)</option>
                                            </select>
                                        </div>

                                        <button type="submit" 
                                                class="w-full px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition mt-4">
                                            Send Invitation
                                        </button>
                                    </form>
                                @else
                                    <p class="text-xs text-text-muted leading-relaxed">Only the Trip Organizer is authorized to invite new members or change permission roles.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Manage Members -->
                        <div class="lg:col-span-2">
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-lg mb-4">Planning Team ({{ $this->users->count() }})</h3>
                                <div class="divide-y divide-border-light">
                                    @foreach ($this->users as $u)
                                        @php
                                            $uRole = $trip->getUserRole($u);
                                        @endphp
                                        <div class="flex items-center justify-between py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="h-10 w-10 rounded-full bg-bg-secondary border border-border-light flex items-center justify-center font-bold text-sm uppercase text-text-main">
                                                    {{ $u->name[0] }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-text-main">{{ $u->name }}</p>
                                                    <p class="text-xs text-text-muted">{{ $u->email }}</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Role dropdown or static label -->
                                            <div class="flex items-center space-x-2">
                                                @if ($trip->canManageMembers(auth()->user()) && $u->id !== auth()->id() && !$u->isSystemAdmin())
                                                    <select wire:change="updateUserRole({{ $u->id }}, $event.target.value)"
                                                            class="text-xs border border-border-card rounded bg-bg-primary text-text-main px-2 py-1">
                                                        <option value="organizer" {{ $uRole === 'organizer' ? 'selected' : '' }}>Organizer</option>
                                                        <option value="co_planner" {{ $uRole === 'co_planner' ? 'selected' : '' }}>Co-Planner</option>
                                                        <option value="member" {{ $uRole === 'member' ? 'selected' : '' }}>Member</option>
                                                        <option value="viewer" {{ $uRole === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                    </select>
                                                    
                                                    <button wire:click="removeUser({{ $u->id }})" 
                                                            wire:confirm="Are you sure you want to remove this user from the trip?"
                                                            class="text-text-muted hover:text-red-600 transition p-1" title="Remove User">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted bg-bg-secondary px-2.5 py-1 rounded border border-border-light capitalize">
                                                        {{ $uRole }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- DOCUMENTS TAB -->
            @if ($activeTab === 'documents')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left: Upload Document -->
                        <div class="lg:col-span-1">
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-lg mb-4">Upload Ticket / PDF</h3>
                                
                                <form wire:submit.prevent="uploadDocument" class="space-y-4">
                                    <div class="border-2 border-dashed border-border-card rounded-xl p-6 text-center hover:border-text-muted transition relative">
                                        <input type="file" wire:model="document_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        <svg class="mx-auto h-8 w-8 text-text-muted stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="mt-2 text-xs font-semibold text-text-main">
                                            @if ($document_file)
                                                Selected: {{ $document_file->getClientOriginalName() }}
                                            @else
                                                Click or Drag PDF/Image here
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-text-muted mt-1">Supports images and PDFs up to 10MB</p>
                                    </div>
                                    @error('document_file') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror

                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition mt-4"
                                            @if(!$document_file) disabled @endif>
                                        Upload Document
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Right: Shared Documents List -->
                        <div class="lg:col-span-2">
                            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold text-lg mb-4">Shared Documents</h3>
                                @if ($this->documents->isEmpty())
                                    <p class="text-sm text-text-muted">No files shared yet.</p>
                                @else
                                    <div class="divide-y divide-border-light">
                                        @foreach ($this->documents as $doc)
                                            <div class="flex items-center justify-between py-4">
                                                <div class="flex items-center space-x-3">
                                                    <span class="p-2 bg-bg-secondary border border-border-light rounded-lg text-lg flex items-center justify-center">
                                                        📄
                                                    </span>
                                                    <div>
                                                        <h4 class="font-bold text-sm text-text-main">{{ $doc->name }}</h4>
                                                        <p class="text-xs text-text-muted">
                                                            {{ round($doc->file_size / 1024, 1) }} KB &bull; Uploaded by: {{ $doc->uploader->name }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <button wire:click="downloadDocument({{ $doc->id }})" 
                                                            class="text-xs font-semibold px-3 py-1.5 border border-border-card rounded-lg hover:bg-bg-secondary transition">
                                                        Download
                                                    </button>
                                                    
                                                    @if (auth()->id() === $doc->uploaded_by || $trip->canManageMembers(auth()->user()))
                                                        <button wire:click="deleteDocument({{ $doc->id }})" 
                                                                wire:confirm="Delete this file permanently?"
                                                                class="text-text-muted hover:text-red-600 transition p-1">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- DISCUSSION TAB -->
            @if ($activeTab === 'discussion')
                <div class="space-y-6" wire:poll.5s>
                    <!-- Post Top-level Comment -->
                    <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                        <h3 class="font-bold text-lg mb-4">Group Discussion</h3>
                        <form wire:submit.prevent="addComment" class="space-y-3">
                            <textarea wire:model="new_comment_content" rows="3" placeholder="Share itinerary ideas, flight deals, or ask group questions..."
                                      class="block w-full px-4 py-3 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral"></textarea>
                            @error('new_comment_content') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-xs rounded-xl shadow-none transition">
                                    Post Message
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Thread/Comments List -->
                    @if ($this->comments->isEmpty())
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-12 text-center shadow-sm">
                            <p class="text-sm text-text-muted">No messages posted yet. Start the brainstorming!</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($this->comments as $comment)
                                <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm space-y-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-bg-secondary border border-border-light flex items-center justify-center font-bold text-xs uppercase text-text-main">
                                                {{ $comment->user->name[0] }}
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-sm text-text-main">{{ $comment->user->name }}</h4>
                                                <span class="text-[10px] text-text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex space-x-2">
                                            <button wire:click="$set('replying_to_id', {{ $comment->id }})" 
                                                    class="text-xs text-text-muted hover:text-text-main underline focus:outline-none transition">
                                                Reply
                                            </button>
                                            
                                            @if (auth()->id() === $comment->user_id || $trip->canManageMembers(auth()->user()))
                                                <button wire:click="deleteComment({{ $comment->id }})" 
                                                        class="text-text-muted hover:text-red-600 transition p-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-text-main leading-relaxed pl-11">{{ $comment->content }}</p>

                                    <!-- Replies listing -->
                                    @if ($comment->replies->isNotEmpty())
                                        <div class="pl-11 border-l border-border-light space-y-4 mt-4">
                                            @foreach ($comment->replies as $reply)
                                                <div class="space-y-2">
                                                    <div class="flex justify-between items-start">
                                                        <div class="flex items-center space-x-2">
                                                            <div class="h-6 w-6 rounded-full bg-bg-secondary border border-border-light flex items-center justify-center font-bold text-[10px] uppercase text-text-main">
                                                                {{ $reply->user->name[0] }}
                                                            </div>
                                                            <div>
                                                                <h5 class="font-bold text-xs text-text-main">{{ $reply->user->name }}</h5>
                                                                <span class="text-[9px] text-text-muted">{{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>
                                                        </div>
                                                        @if (auth()->id() === $reply->user_id || $trip->canManageMembers(auth()->user()))
                                                            <button wire:click="deleteComment({{ $reply->id }})" 
                                                                    class="text-text-muted hover:text-red-600 transition p-1">
                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-text-main pl-8">{{ $reply->content }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Reply box inline toggled -->
                                    @if ($replying_to_id === $comment->id)
                                        <div class="pl-11 mt-4 border-l border-brand-neutral pt-2">
                                            <form wire:submit.prevent="addReply({{ $comment->id }})" class="space-y-2">
                                                <input type="text" wire:model="new_reply_content" placeholder="Write a reply..."
                                                       class="block w-full px-3 py-1.5 border border-border-card rounded-lg text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                                <div class="flex justify-end space-x-2">
                                                    <button type="button" wire:click="$set('replying_to_id', null)" 
                                                            class="px-3 py-1 border border-border-card rounded-lg text-[10px] hover:bg-bg-secondary transition">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" 
                                                            class="px-3 py-1 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-[10px] font-semibold rounded-lg transition">
                                                        Reply
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>

    <!-- ITINERARY MODAL -->
    @if ($showItineraryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
            <div class="bg-bg-primary border border-border-light w-full max-w-lg rounded-2xl shadow-xl p-8 animate-fade-in font-sans">
                <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                    <h2 class="text-xl font-bold text-text-main">Add Activity to Itinerary</h2>
                    <button type="button" wire:click="$set('showItineraryModal', false)" class="text-text-muted hover:text-text-main transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="addItineraryItem" class="space-y-4">
                    <div>
                        <label for="itinerary_title" class="block text-sm font-semibold text-text-main">Activity Title</label>
                        <input type="text" id="itinerary_title" wire:model="itinerary_title" placeholder="e.g. Flight to Barcelona"
                               class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        @error('itinerary_title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="itinerary_datetime" class="block text-sm font-semibold text-text-main">Date and Time</label>
                            <input type="datetime-local" id="itinerary_datetime" wire:model="itinerary_datetime"
                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                            @error('itinerary_datetime') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="itinerary_category" class="block text-sm font-semibold text-text-main">Category</label>
                            <select id="itinerary_category" wire:model="itinerary_category"
                                    class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                                <option value="activity">Activity / Tour</option>
                                <option value="transport">Transport</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="food">Food & Beverage</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="itinerary_location" class="block text-sm font-semibold text-text-main">Location</label>
                            <input type="text" id="itinerary_location" wire:model="itinerary_location" placeholder="e.g. Terminal 1"
                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        </div>

                        <div>
                            <label for="itinerary_duration" class="block text-sm font-semibold text-text-main">Duration (minutes)</label>
                            <input type="number" id="itinerary_duration" wire:model="itinerary_duration"
                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        </div>
                    </div>

                    <div>
                        <label for="itinerary_cost" class="block text-sm font-semibold text-text-main">Estimated Cost ($)</label>
                        <input type="number" step="0.01" id="itinerary_cost" wire:model="itinerary_cost" placeholder="e.g. 120"
                               class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        @error('itinerary_cost') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="itinerary_desc" class="block text-sm font-semibold text-text-main">Notes & Reminders</label>
                        <textarea id="itinerary_desc" wire:model="itinerary_desc" rows="3" placeholder="Add reservation codes, ticket urls, or guidelines..."
                                  class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral"></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" wire:click="$set('showItineraryModal', false)" 
                                class="px-5 py-2.5 border border-border-card rounded-xl text-sm font-semibold text-text-main hover:bg-bg-secondary transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition">
                            Save Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- EXPENSE MODAL -->
    @if ($showExpenseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
            <div class="bg-bg-primary border border-border-light w-full max-w-lg rounded-2xl shadow-xl p-8 animate-fade-in font-sans">
                <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                    <h2 class="text-xl font-bold text-text-main">Record Group Expense</h2>
                    <button type="button" wire:click="$set('showExpenseModal', false)" class="text-text-muted hover:text-text-main transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="addExpense" class="space-y-4">
                    <div>
                        <label for="expense_title" class="block text-sm font-semibold text-text-main">Expense Description</label>
                        <input type="text" id="expense_title" wire:model="expense_title" placeholder="e.g. Group Dinner"
                               class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        @error('expense_title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="expense_amount" class="block text-sm font-semibold text-text-main">Amount ($)</label>
                            <input type="number" step="0.01" id="expense_amount" wire:model="expense_amount" placeholder="e.g. 150.00"
                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                            @error('expense_amount') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="expense_date" class="block text-sm font-semibold text-text-main">Date</label>
                            <input type="date" id="expense_date" wire:model="expense_date"
                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="expense_paid_by" class="block text-sm font-semibold text-text-main">Who Paid?</label>
                            <select id="expense_paid_by" wire:model="expense_paid_by"
                                    class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                                @foreach($this->users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="expense_category" class="block text-sm font-semibold text-text-main">Category</label>
                            <select id="expense_category" wire:model="expense_category"
                                    class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                                <option value="food">Food & Dining</option>
                                <option value="transport">Transport</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="activities">Activities</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                    </div>

                    <!-- Splits checklist selection -->
                    <div>
                        <label class="block text-sm font-semibold text-text-main mb-2">Split with whom?</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto border border-border-card rounded-xl p-3 bg-bg-primary">
                            @foreach ($this->users as $u)
                                <label class="flex items-center space-x-2 text-sm cursor-pointer">
                                    <input type="checkbox" wire:model.live="selected_members" value="{{ $u->id }}"
                                           class="rounded border-border-card text-brand-neutral focus:ring-brand-neutral h-4 w-4">
                                    <span>{{ $u->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selected_members') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" wire:click="$set('showExpenseModal', false)" 
                                class="px-5 py-2.5 border border-border-card rounded-xl text-sm font-semibold text-text-main hover:bg-bg-secondary transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition">
                            Add Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- POLL MODAL -->
    @if ($showPollModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
            <div class="bg-bg-primary border border-border-light w-full max-w-lg rounded-2xl shadow-xl p-8 animate-fade-in font-sans">
                <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                    <h2 class="text-xl font-bold text-text-main">Create a Poll</h2>
                    <button type="button" wire:click="$set('showPollModal', false)" class="text-text-muted hover:text-text-main transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createPoll" class="space-y-4">
                    <div>
                        <label for="poll_title" class="block text-sm font-semibold text-text-main">Poll Question</label>
                        <input type="text" id="poll_title" wire:model="poll_title" placeholder="e.g. Which hotel should we book?"
                               class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        @error('poll_title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="poll_type" class="block text-sm font-semibold text-text-main">Poll Type</label>
                            <select id="poll_type" wire:model="poll_type"
                                    class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                                <option value="general">General Choice</option>
                                <option value="destination">Destination Selection</option>
                                <option value="hotel">Hotel Selection</option>
                                <option value="dates">Travel Date Selection</option>
                                <option value="activity">Activity Preference</option>
                            </select>
                        </div>

                        <div>
                            <label for="poll_desc" class="block text-sm font-semibold text-text-main">Details (optional)</label>
                            <input type="text" id="poll_desc" wire:model="poll_desc" placeholder="Brief context..."
                                   class="mt-1 block w-full px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                        </div>
                    </div>

                    <!-- Dynamic Options Input List -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-text-main">Options</label>
                        @foreach ($poll_options as $index => $option)
                            <div class="flex items-center space-x-2">
                                <input type="text" wire:model="poll_options.{{ $index }}" placeholder="Option {{ $index + 1 }}"
                                       class="flex-grow px-4 py-2 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral">
                                
                                @if (count($poll_options) > 2)
                                    <button type="button" wire:click="removePollOptionField({{ $index }})" 
                                            class="text-text-muted hover:text-red-600 transition">
                                        &times;
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        @error('poll_options.*') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror
                        
                        <button type="button" wire:click="addPollOptionField" 
                                class="text-xs font-semibold text-text-muted hover:text-text-main underline mt-1 block">
                            + Add Option Field
                        </button>
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" wire:click="$set('showPollModal', false)" 
                                class="px-5 py-2.5 border border-border-card rounded-xl text-sm font-semibold text-text-main hover:bg-bg-secondary transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition">
                            Create Poll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
