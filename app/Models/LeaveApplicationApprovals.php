<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplicationApprovals extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'approval_id',
        'employee_id',
        'user_id',
        'leave_policy_id',
        'date',
        'step',
        'approval_status',
        'step_flag'
    ];

    protected $casts = [];
}
