<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveCutExplanation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'leave_policy_id',
        'leave_balance_id',
        'fiscal_year_id',
        'total_cutting_days',
        'note'
    ];
}
