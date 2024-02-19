<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_id',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
