<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'company_id',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
