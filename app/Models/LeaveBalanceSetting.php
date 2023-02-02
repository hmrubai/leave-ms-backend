<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'employment_type_id',
        'leave_policy_id',
        'total_days',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
