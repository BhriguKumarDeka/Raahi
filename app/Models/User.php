<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'onboarded', 'profile_image', 'travel_style', 'budget_preference', 'activity_interests', 'preferred_destinations'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'onboarded' => 'boolean',
            'travel_style' => 'array',
            'activity_interests' => 'array',
            'preferred_destinations' => 'array',
        ];
    }

    protected static function booted()
    {
        // Cascade cleanup when a user is deleted
        static::deleting(function ($user) {
            $user->votes()->delete();
            $user->splits()->delete();
            $user->invitations()->delete();
            // Delete comments (replies cascade via Comment::booted)
            \App\Models\Comment::where('user_id', $user->id)->each(fn($c) => $c->delete());
            // Detach from all trips
            $user->trips()->detach();
        });
    }

    public function isSystemAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function trips()
    {
        return $this->belongsToMany(Trip::class)->withPivot('role')->withTimestamps();
    }

    public function createdTrips()
    {
        return $this->hasMany(Trip::class, 'creator_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'paid_by');
    }

    public function splits()
    {
        return $this->hasMany(ExpenseUser::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }
}
