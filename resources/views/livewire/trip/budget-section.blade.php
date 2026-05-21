<?php

use App\Models\Trip;
use App\Models\Expense;
use App\Models\ExpenseUser;
use function Livewire\Volt\{state, computed, on, mount};

state([
    'trip',
    'showExpenseModal' => false,
    'expense_title' => '',
    'expense_amount' => '',
    'expense_paid_by' => '',
    'expense_category' => 'miscellaneous',
    'expense_date' => '',
    'selected_members' => [], // who shares the cost
]);

on(['trip-updated' => '$refresh']);

mount(function () {
    $this->expense_paid_by = auth()->id();
    $this->expense_date = date('Y-m-d');
    $this->selected_members = $this->trip->users->pluck('id')->toArray();
});

$expenses = computed(function () {
    return $this->trip->expenses()->with('payer', 'splits.user')->get();
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

    $tripExpenses = $this->trip->expenses()->with('splits').get();

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
    $this->dispatch('trip-updated');
};

$deleteExpense = function ($id) {
    if (!$this->trip->canManageExpenses(auth()->user())) {
        abort(403);
    }

    Expense::destroy($id);
    $this->dispatch('trip-updated');
};

?>

<div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], y: [10, 0] }, { duration: 0.3 }) }">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left side: ledger stats & settlement calculations -->
        <div class="md:col-span-1 space-y-6">
            
            <!-- Net Balances List -->
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main border-b border-border-light pb-2 mb-3">Balances</h3>
                <div class="space-y-3">
                    @foreach ($this->settlements['balances'] as $memberId => $balanceData)
                        <div class="flex justify-between items-center text-xs py-1.5 border-b border-border-light/70 last:border-b-0">
                            <span class="font-bold text-text-main">{{ $balanceData['user']->name }}</span>
                            @php
                                $bal = round($balanceData['balance'], 2);
                            @endphp
                            <span class="font-extrabold {{ $bal >= 0 ? 'text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100' : 'text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100' }}">
                                {{ $bal >= 0 ? '+' : '' }}₹{{ number_format($bal, 0) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Net settlements recommendations -->
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main border-b border-border-light pb-2 mb-3">Suggested Settlement</h3>
                @if (empty($this->settlements['instructions']))
                    <p class="text-xs text-text-muted py-2 flex items-center space-x-1.5">
                        <i class="ph-bold ph-check-circle text-emerald-600 text-sm"></i>
                        <span>All balances are settled!</span>
                    </p>
                @else
                    <div class="space-y-2.5">
                        @foreach ($this->settlements['instructions'] as $inst)
                            <div class="text-xs p-3 bg-bg-secondary border border-border-light rounded-xl flex flex-col gap-1">
                                <span class="text-text-muted"><strong class="text-text-main">{{ $inst['from']->name }}</strong> pays</span>
                                <span class="font-extrabold text-brand-neutral text-sm">₹{{ number_format($inst['amount'], 0) }}</span>
                                <span class="text-[10px] text-text-muted">to <strong class="text-text-main">{{ $inst['to']->name }}</strong></span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Category Breakdown Chart -->
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main border-b border-border-light pb-2 mb-3">Expenses by Category</h3>
                @php
                    $categoryTotals = [];
                    $totalAmount = 0;
                    foreach ($this->expenses as $exp) {
                        $cat = strtolower($exp->category ?: 'other');
                        $categoryTotals[$cat] = ($categoryTotals[$cat] ?? 0) + $exp->amount;
                        $totalAmount += $exp->amount;
                    }
                    
                    $icons = [
                        'transport' => 'ph-airplane',
                        'accommodation' => 'ph-bed',
                        'food' => 'ph-fork-knife',
                        'activities' => 'ph-ticket',
                        'other' => 'ph-package'
                    ];
                @endphp
                @if ($totalAmount == 0)
                    <p class="text-xs text-text-muted py-2">No expenses recorded yet.</p>
                @else
                    <div class="space-y-3.5">
                        @foreach (['transport', 'accommodation', 'food', 'activities', 'miscellaneous' => 'other'] as $key => $cat)
                            @php
                                $catName = is_numeric($key) ? $cat : $key;
                                $amt = $categoryTotals[$catName] ?? 0;
                                $pct = $totalAmount > 0 ? ($amt / $totalAmount) * 100 : 0;
                                $iconName = $icons[$cat] ?? 'ph-package';
                            @endphp
                            @if ($amt > 0)
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[11px] font-bold">
                                        <span class="flex items-center space-x-1 capitalize">
                                            <i class="ph {{ $iconName }} text-brand-neutral"></i>
                                            <span>{{ $catName }}</span>
                                        </span>
                                        <span class="text-text-muted">₹{{ number_format($amt, 0) }} ({{ round($pct) }}%)</span>
                                    </div>
                                    <div class="w-full bg-bg-secondary h-1.5 rounded-full overflow-hidden border border-border-light">
                                        <div class="bg-brand-neutral h-full rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right side: expenses lists -->
        <div class="md:col-span-2 space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold font-sans-display text-text-main">Shared Bill Log</h3>
                @if ($trip->canManageExpenses(auth()->user()))
                    <button type="button" 
                            wire:click="$set('showExpenseModal', true)"
                            class="flex items-center space-x-2 px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl shadow-none focus:outline-none transition cursor-pointer">
                        <i class="ph ph-receipt text-sm"></i>
                        <span>Log a shared bill</span>
                    </button>
                @endif
            </div>

            @if ($this->expenses->isEmpty())
                <div class="bg-bg-primary border border-border-light rounded-3xl p-16 text-center shadow-sm">
                    <i class="ph-duotone ph-wallet text-5xl text-text-muted block mx-auto"></i>
                    <h4 class="text-base font-bold mt-4">No expenses recorded</h4>
                    <p class="text-xs text-text-muted mt-1 max-w-xs mx-auto">Keep track of flight bookings, dinners, and accommodation. Everyone's balance is calculated automatically.</p>
                </div>
            @else
                <div class="bg-bg-primary border border-border-light rounded-2xl overflow-hidden shadow-sm">
                    <div class="divide-y divide-border-light">
                        @foreach ($this->expenses as $exp)
                            <div class="p-5 flex justify-between items-center hover:bg-bg-secondary/40 transition">
                                <div>
                                    <h4 class="font-bold text-sm text-text-main">{{ $exp->title }}</h4>
                                    <p class="text-xs text-text-muted mt-1 flex items-center space-x-2">
                                        <span>Paid by <strong class="text-text-main">{{ $exp->payer->name }}</strong></span>
                                        <span class="text-border-card">&bull;</span>
                                        <span>{{ $exp->date->format('M d, Y') }}</span>
                                        <span class="text-border-card">&bull;</span>
                                        <span class="capitalize">{{ $exp->category }}</span>
                                    </p>
                                    
                                    <!-- Splits badges -->
                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                        @foreach($exp->splits as $split)
                                            <span class="bg-bg-secondary border border-border-light text-[9px] px-2 py-0.5 rounded-full text-text-muted font-bold">
                                                {{ $split->user->name }}: ₹{{ number_format($split->share, 0) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 shrink-0">
                                    <span class="font-extrabold text-base text-brand-neutral">₹{{ number_format($exp->amount, 0) }}</span>
                                    
                                    @if ($trip->canManageExpenses(auth()->user()))
                                        <button wire:click="deleteExpense({{ $exp->id }})" 
                                                wire:confirm="Delete this expense from ledger?"
                                                class="text-text-muted hover:text-red-600 transition p-1.5 bg-bg-secondary hover:bg-red-50 rounded-lg cursor-pointer">
                                            <i class="ph ph-trash text-sm block"></i>
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

    <!-- Record Expense Modal -->
    @if ($showExpenseModal)
        <div class="fixed inset-0 bg-bg-secondary/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1] }, { duration: 0.25 }) }">
            <div class="bg-bg-primary border border-border-card rounded-3xl w-full max-w-lg p-6 sm:p-8 shadow-[0_20px_50px_rgba(26,59,43,0.08)] relative"
                 x-init="if(window.Motion) { window.Motion.animate($el, { scale: [0.95, 1], opacity: [0, 1] }, { duration: 0.25 }) }">
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-brand-neutral font-sans-display flex items-center space-x-2">
                        <i class="ph ph-receipt text-lg"></i>
                        <span>Log a shared bill</span>
                    </h2>
                    <button type="button" wire:click="$set('showExpenseModal', false)" class="text-text-muted hover:text-text-main transition cursor-pointer p-1">
                        <i class="ph ph-x text-lg block"></i>
                    </button>
                </div>

                <form wire:submit.prevent="addExpense" class="space-y-4">
                    <div>
                        <label for="expense_title" class="block text-xs font-bold text-text-main">Bill Description</label>
                        <input type="text" id="expense_title" wire:model="expense_title" placeholder="e.g. Airbnb Booking, Dinner at Cafe"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        @error('expense_title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="expense_amount" class="block text-xs font-bold text-text-main">Total Amount (₹)</label>
                            <input type="number" id="expense_amount" wire:model="expense_amount" step="0.01" placeholder="0.00"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                            @error('expense_amount') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="expense_paid_by" class="block text-xs font-bold text-text-main">Paid By</label>
                            <select id="expense_paid_by" wire:model="expense_paid_by"
                                    class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                @foreach($trip->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_paid_by') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="expense_category" class="block text-xs font-bold text-text-main">Expense Category</label>
                            <select id="expense_category" wire:model="expense_category"
                                    class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                                <option value="accommodation">Accommodation</option>
                                <option value="transport">Transport</option>
                                <option value="food">Food & Dining</option>
                                <option value="activities">Activities</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                        </div>

                        <div>
                            <label for="expense_date" class="block text-xs font-bold text-text-main">Date Paid</label>
                            <input type="date" id="expense_date" wire:model="expense_date"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                            @error('expense_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-text-main mb-2">Split Equally With</span>
                        <div class="bg-bg-secondary/40 border border-border-light rounded-2xl p-4 max-h-[140px] overflow-y-auto space-y-2.5">
                            @foreach($trip->users as $user)
                                <label class="flex items-center space-x-2.5 text-xs text-text-main cursor-pointer">
                                    <input type="checkbox" value="{{ $user->id }}" wire:model.live="selected_members"
                                           class="rounded border-border-card text-brand-neutral focus:ring-brand-neutral">
                                    <span>{{ $user->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selected_members') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" wire:click="$set('showExpenseModal', false)" 
                                class="px-5 py-2.5 border border-border-card rounded-xl text-xs font-bold text-text-main hover:bg-bg-secondary transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl transition cursor-pointer">
                            Log bill
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
