<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'leave_policy_id',
        'fiscal_year_id',
        'total_days',
        'availed_days',
        'remaining_days',
        'carry_forward_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplications::class, 'leave_policy_id', 'leave_policy_id');
    }
}
