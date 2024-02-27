<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsepBalanceAddedHistory extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'month_in_number',
        'year',
        'added_by',
        'added_at'
    ];

    protected $casts = [];
}
