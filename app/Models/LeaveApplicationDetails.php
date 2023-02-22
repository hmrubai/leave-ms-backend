<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplicationDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'employee_id',
        'user_id',
        'leave_policy_id',
        'date'
    ];

    protected $casts = [];
}
