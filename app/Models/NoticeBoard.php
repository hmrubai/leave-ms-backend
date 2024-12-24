<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'attachment',
        'created_by',
        'notice_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
