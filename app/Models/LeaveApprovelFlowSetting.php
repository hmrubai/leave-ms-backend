<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApprovelFlowSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'approval_authority_id',
        'step',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
