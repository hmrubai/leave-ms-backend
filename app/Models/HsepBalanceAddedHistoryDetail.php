<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsepBalanceAddedHistoryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'hsep_balance_added_history_id',
        'employee_id',
        'user_id',
        'leave_policy_id',
        'added_balances'
    ];

    protected $casts = [];
}
