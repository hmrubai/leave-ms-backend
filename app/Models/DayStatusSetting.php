<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayStatusSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'saturday',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
