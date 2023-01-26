<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_no',
        'company_email',
        'hr_email',
        'leave_email',
        'company_logo',
        'employee_code_length',
        'company_prefix',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
