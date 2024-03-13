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
        'wing_id',
        'wing',
        'is_hsep',
        'employment_type_id',
        'division_id',
        'district_id',
        'city_id',
        'area_id',
        'is_stuckoff',
        'stuckoff_date',
        'office_contact_number',
        'finger_print_id',
        'personal_alt_contact_number',
        'personal_email',
        'passport_number',
        'spouse_name',
        'spouse_number',
        'fathers_contact_number',
        'mothers_contact_number',
        'referee_office',
        'referee_relative',
        'referee_contact_details',
        'key_skills',
        'highest_level_of_study',
        'e_tin',
        'applicable_tax_amount',
        'official_achievement',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'stuckoff_date' => 'date',
        'is_stuckoff' => 'boolean',
        'is_hsep' => 'boolean',
        'is_active' => 'boolean',
    ];
}
