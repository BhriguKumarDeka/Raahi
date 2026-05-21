<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'user_id',
        'parent_id',
        'content',
    ];

    protected static function booted()
    {
        // Cascade delete: remove child replies when a parent comment is deleted
        static::deleting(function ($comment) {
            $comment->replies()->each(fn ($reply) => $reply->delete());
        });
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }
}
