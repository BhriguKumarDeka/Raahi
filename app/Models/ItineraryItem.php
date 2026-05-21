<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItineraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'title',
        'description',
        'datetime',
        'location',
        'duration_minutes',
        'cost',
        'category',
        'added_by',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'cost' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
