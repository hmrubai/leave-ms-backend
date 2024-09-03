<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'finger_print_id',
        'log_date',
        'punch_log',
        'start_time',
        'end_time',
        'total_time',
        'is_processed'
    ];

    protected $casts = [
        'is_processed' => 'boolean'
    ];
}
