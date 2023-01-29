<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'company_id',
        'leave_title',
        'leave_short_code',
        'total_days',
        'is_applicable_for_all',
        'applicable_for',
        'is_leave_cut_applicable',
        'is_carry_forward',
        'is_document_upload',
        'is_holiday_deduct',
        'document_upload_after_days',
        'max_carry_forward_days',
        'is_active',
    ];

    protected $casts = [
        'is_applicable_for_all' => 'boolean',
        'is_leave_cut_applicable' => 'boolean',
        'is_carry_forward' => 'boolean',
        'is_document_upload' => 'boolean',
        'is_holiday_deduct' => 'boolean',
        'is_active' => 'boolean',
    ];
}
