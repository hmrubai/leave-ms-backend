<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeInfo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'father_name',
        'mother_name',
        'employee_id',
        'employee_code',
        'email',
        'mobile',
        'nid',
        'present_address',
        'permanent_address',
        'date_of_birth',
        'joining_date',
        'blood_group',
        'marital_status',
        'gender',
        'company_id',
        'branch_id',
        'department_id',
        'designation_id',
        'division_id',
        'district_id',
        'city_id',
        'area_id',
        'is_stuckoff',
        'stuckoff_date',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'stuckoff_date' => 'date',
        'is_stuckoff' => 'boolean',
        'is_active' => 'boolean',
    ];
}
