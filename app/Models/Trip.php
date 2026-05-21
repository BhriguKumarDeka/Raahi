<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'destination',
        'start_date',
        'end_date',
        'description',
        'budget_estimate',
        'creator_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget_estimate' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function itineraryItems()
    {
        return $this->hasMany(ItineraryItem::class)->orderBy('datetime', 'asc');
    }

    public function polls()
    {
        return $this->hasMany(Poll::class)->orderBy('created_at', 'desc');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class)->orderBy('date', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class)->orderBy('created_at', 'desc');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the role of a user in this trip.
     */
    public function getUserRole(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        // System Admin has full control
        if ($user->isSystemAdmin()) {
            return 'organizer';
        }

        $tripUser = $this->users()->where('user_id', $user->id)->first();

        return $tripUser ? $tripUser->pivot->role : null;
    }

    /**
     * Check if user can modify itinerary.
     */
    public function canEditItinerary(?User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['organizer', 'co_planner']);
    }

    /**
     * Check if user can manage members.
     */
    public function canManageMembers(?User $user): bool
    {
        $role = $this->getUserRole($user);
        return $role === 'organizer';
    }

    /**
     * Check if user can manage expenses.
     */
    public function canManageExpenses(?User $user): bool
    {
        $role = $this->getUserRole($user);
        return $role === 'organizer';
    }

    /**
     * Check if user can create polls.
     */
    public function canCreatePolls(?User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['organizer']);
    }

    /**
     * Check if user can vote.
     */
    public function canVote(?User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['organizer', 'co_planner', 'member']);
    }

    /**
     * Check if user can view details.
     */
    public function canView(?User $user): bool
    {
        return $this->getUserRole($user) !== null;
    }
}
