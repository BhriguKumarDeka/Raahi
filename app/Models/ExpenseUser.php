<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseUser extends Model
{
    use HasFactory;

    protected $table = 'expense_user';

    protected $fillable = [
        'expense_id',
        'user_id',
        'share',
        'is_paid',
    ];

    protected $casts = [
        'share' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
