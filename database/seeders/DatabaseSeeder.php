<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Trip;
use App\Models\ItineraryItem;
use App\Models\Expense;
use App\Models\ExpenseUser;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@raahi.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'onboarded' => true,
            'travel_style' => ['cultural', 'luxury'],
            'budget_preference' => 'medium',
            'activity_interests' => ['food', 'sightseeing'],
            'preferred_destinations' => ['Kyoto', 'Paris'],
        ]);

        $aditi = User::create([
            'name' => 'Aditi Sharma',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'onboarded' => true,
            'travel_style' => ['budget', 'adventure'],
            'budget_preference' => 'low',
            'activity_interests' => ['hiking', 'backpacking'],
            'preferred_destinations' => ['Bali', 'Manali'],
        ]);

        $rohan = User::create([
            'name' => 'Rohan Gupta',
            'email' => 'rohan@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'onboarded' => true,
            'travel_style' => ['adventure', 'nature'],
            'budget_preference' => 'medium',
            'activity_interests' => ['hiking', 'camping', 'diving'],
            'preferred_destinations' => ['Iceland', 'Bali'],
        ]);

        $priya = User::create([
            'name' => 'Priya Patel',
            'email' => 'priya@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'onboarded' => true,
            'travel_style' => ['relaxing', 'cultural'],
            'budget_preference' => 'high',
            'activity_interests' => ['food', 'shopping', 'museums'],
            'preferred_destinations' => ['Tokyo', 'New York'],
        ]);

        $kabir = User::create([
            'name' => 'Kabir Malhotra',
            'email' => 'kabir@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'onboarded' => false, // Will trigger onboarding wizard redirection!
        ]);

        // 2. Create Trip 1: Summer in Bali
        $baliTrip = Trip::create([
            'name' => 'Summer in Bali',
            'destination' => 'Bali, Indonesia',
            'start_date' => Carbon::now()->addDays(45),
            'end_date' => Carbon::now()->addDays(55),
            'description' => 'A tropical getaway focusing on beach relaxing, culture tours in Ubud, and scuba diving in Nusa Penida.',
            'budget_estimate' => 2500.00,
            'creator_id' => $aditi->id,
        ]);

        // Attach participants
        $baliTrip->users()->attach($aditi->id, ['role' => 'organizer']);
        $baliTrip->users()->attach($rohan->id, ['role' => 'co_planner']);
        $baliTrip->users()->attach($priya->id, ['role' => 'member']);

        // Bali Itinerary Items
        ItineraryItem::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Flight to Denpasar',
            'description' => 'Singapore Airlines SQ942. Meet at Terminal 3.',
            'datetime' => Carbon::now()->addDays(45)->setHour(10)->setMinute(0),
            'location' => 'Singapore Changi Airport',
            'duration_minutes' => 180,
            'cost' => 450.00,
            'category' => 'transport',
            'added_by' => $aditi->id,
        ]);

        ItineraryItem::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Check-in Seminyak Beachfront Villa',
            'description' => 'Key code is 4821. Welcome drinks included.',
            'datetime' => Carbon::now()->addDays(45)->setHour(14)->setMinute(0),
            'location' => 'Seminyak Beach Road',
            'duration_minutes' => 60,
            'cost' => 800.00,
            'category' => 'accommodation',
            'added_by' => $aditi->id,
        ]);

        ItineraryItem::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Sunset Jimbaran Seafood Dinner',
            'description' => 'Table booked under Aditi. Sunset at 6:15 PM.',
            'datetime' => Carbon::now()->addDays(46)->setHour(18)->setMinute(30),
            'location' => 'Jimbaran Bay Seafood',
            'duration_minutes' => 120,
            'cost' => 120.00,
            'category' => 'food',
            'added_by' => $priya->id,
        ]);

        ItineraryItem::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Scuba Diving Nusa Penida',
            'description' => 'Manta Point dive. Remember to bring dry bags!',
            'datetime' => Carbon::now()->addDays(47)->setHour(8)->setMinute(0),
            'location' => 'Sanur Harbour Departure',
            'duration_minutes' => 360,
            'cost' => 150.00,
            'category' => 'activity',
            'added_by' => $rohan->id,
        ]);

        // Bali Expenses and Splits
        // Expense 1: Villa Booking (Paid by Aditi, split equally among all 3)
        $expenseVilla = Expense::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Seminyak Villa Downpayment',
            'amount' => 800.00,
            'paid_by' => $aditi->id,
            'split_type' => 'equal',
            'category' => 'accommodation',
            'date' => Carbon::now()->subDays(5),
        ]);
        foreach ([$aditi->id, $rohan->id, $priya->id] as $uid) {
            ExpenseUser::create([
                'expense_id' => $expenseVilla->id,
                'user_id' => $uid,
                'share' => 266.67,
                'is_paid' => $uid == $aditi->id,
            ]);
        }

        // Expense 2: Jimbaran Dinner (Paid by Priya, split equally)
        $expenseDinner = Expense::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Jimbaran Seafood Dinner',
            'amount' => 120.00,
            'paid_by' => $priya->id,
            'split_type' => 'equal',
            'category' => 'food',
            'date' => Carbon::now()->subDays(2),
        ]);
        foreach ([$aditi->id, $rohan->id, $priya->id] as $uid) {
            ExpenseUser::create([
                'expense_id' => $expenseDinner->id,
                'user_id' => $uid,
                'share' => 40.00,
                'is_paid' => $uid == $priya->id,
            ]);
        }

        // Expense 3: Scuba Deposit (Paid by Rohan, split equally)
        $expenseScuba = Expense::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Scuba Booking Deposit',
            'amount' => 150.00,
            'paid_by' => $rohan->id,
            'split_type' => 'equal',
            'category' => 'activities',
            'date' => Carbon::now()->subDays(1),
        ]);
        foreach ([$aditi->id, $rohan->id, $priya->id] as $uid) {
            ExpenseUser::create([
                'expense_id' => $expenseScuba->id,
                'user_id' => $uid,
                'share' => 50.00,
                'is_paid' => $uid == $rohan->id,
            ]);
        }

        // Bali Polls & Voting
        // Poll 1: Villa choice (Locked)
        $pollVilla = Poll::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Which Villa style do we want?',
            'description' => 'Choosing between Ubud rainforest or Seminyak beach.',
            'type' => 'accommodation',
            'is_locked' => true,
            'created_by' => $aditi->id,
        ]);
        $optBeach = PollOption::create(['poll_id' => $pollVilla->id, 'option_text' => 'Seminyak Beachfront Villa', 'votes_count' => 2]);
        $optJungle = PollOption::create(['poll_id' => $pollVilla->id, 'option_text' => 'Ubud Jungle Sanctuary', 'votes_count' => 1]);
        Vote::create(['poll_id' => $pollVilla->id, 'poll_option_id' => $optBeach->id, 'user_id' => $aditi->id]);
        Vote::create(['poll_id' => $pollVilla->id, 'poll_option_id' => $optBeach->id, 'user_id' => $priya->id]);
        Vote::create(['poll_id' => $pollVilla->id, 'poll_option_id' => $optJungle->id, 'user_id' => $rohan->id]);

        // Poll 2: Day Trip activity (Unlocked)
        $pollActivity = Poll::create([
            'trip_id' => $baliTrip->id,
            'title' => 'Do we want a Scuba Diving day?',
            'description' => 'Nusa Penida Manta Point. Costs about $150.',
            'type' => 'activity',
            'is_locked' => false,
            'created_by' => $aditi->id,
        ]);
        $optYes = PollOption::create(['poll_id' => $pollActivity->id, 'option_text' => 'Yes, let\'s book Scuba!', 'votes_count' => 2]);
        $optNo = PollOption::create(['poll_id' => $pollActivity->id, 'option_text' => 'No, prefer Snorkeling', 'votes_count' => 1]);
        Vote::create(['poll_id' => $pollActivity->id, 'poll_option_id' => $optYes->id, 'user_id' => $aditi->id]);
        Vote::create(['poll_id' => $pollActivity->id, 'poll_option_id' => $optYes->id, 'user_id' => $rohan->id]);
        Vote::create(['poll_id' => $pollActivity->id, 'poll_option_id' => $optNo->id, 'user_id' => $priya->id]);

        // Bali Comments / Discussion
        $c1 = Comment::create([
            'trip_id' => $baliTrip->id,
            'user_id' => $aditi->id,
            'content' => 'Hey everyone! Welcome to our Bali workspace. I have added the initial flights and villa details. Please review!',
            'parent_id' => null,
        ]);
        Comment::create([
            'trip_id' => $baliTrip->id,
            'user_id' => $rohan->id,
            'content' => 'Villa looks absolutely incredible! Can\'t wait.',
            'parent_id' => $c1->id,
        ]);
        Comment::create([
            'trip_id' => $baliTrip->id,
            'user_id' => $priya->id,
            'content' => 'Thanks Aditi for planning this! Let\'s sort out the dinner options too.',
            'parent_id' => $c1->id,
        ]);


        // 3. Create Trip 2: Manali Weekend Trek
        $manaliTrip = Trip::create([
            'name' => 'Manali Weekend Trek',
            'destination' => 'Manali, India',
            'start_date' => Carbon::now()->addDays(15),
            'end_date' => Carbon::now()->addDays(18),
            'description' => 'Quick weekend mountain escape for hiking in Solang Valley.',
            'budget_estimate' => 400.00,
            'creator_id' => $rohan->id,
        ]);

        $manaliTrip->users()->attach($rohan->id, ['role' => 'organizer']);
        $manaliTrip->users()->attach($aditi->id, ['role' => 'member']);

        // Manali Itinerary Items
        ItineraryItem::create([
            'trip_id' => $manaliTrip->id,
            'title' => 'Overnight Volvo Bus to Manali',
            'description' => 'Boarding from Kashmere Gate ISBT, Delhi.',
            'datetime' => Carbon::now()->addDays(15)->setHour(22)->setMinute(0),
            'location' => 'ISBT Delhi',
            'duration_minutes' => 600,
            'cost' => 25.00,
            'category' => 'transport',
            'added_by' => $rohan->id,
        ]);

        ItineraryItem::create([
            'trip_id' => $manaliTrip->id,
            'title' => 'Backpackers Hostel Check-in',
            'description' => 'Manali Old Town Hostel.',
            'datetime' => Carbon::now()->addDays(16)->setHour(8)->setMinute(0),
            'location' => 'Old Manali',
            'duration_minutes' => 30,
            'cost' => 40.00,
            'category' => 'accommodation',
            'added_by' => $rohan->id,
        ]);

        // Manali Expenses
        $expenseBus = Expense::create([
            'trip_id' => $manaliTrip->id,
            'title' => 'Volvo Bus Tickets Delhi-Manali',
            'amount' => 50.00,
            'paid_by' => $rohan->id,
            'split_type' => 'equal',
            'category' => 'transport',
            'date' => Carbon::now()->subDays(1),
        ]);
        foreach ([$rohan->id, $aditi->id] as $uid) {
            ExpenseUser::create([
                'expense_id' => $expenseBus->id,
                'user_id' => $uid,
                'share' => 25.00,
                'is_paid' => $uid == $rohan->id,
            ]);
        }
    }
}
