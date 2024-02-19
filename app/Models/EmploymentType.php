<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
