<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'uploaded_by',
        'name',
        'file_path',
        'file_size',
        'file_type',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
