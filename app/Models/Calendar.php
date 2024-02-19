<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'year',
        'day_title',
        'day_note',
        'month_in_number',
        'day_type_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
