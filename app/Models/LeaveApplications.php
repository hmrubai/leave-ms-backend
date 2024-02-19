<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplications extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'leave_policy_id',
        'start_date',
        'end_date',
        'total_applied_days',
        'is_half_day',
        'half_day',
        'responsibility_carried_by',
        'leave_status',
        'leave_reason',
        'rejection_cause'
    ];

    protected $casts = [
        'is_half_day' => 'boolean',
        'is_active' => 'boolean'
    ];
}
