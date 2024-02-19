<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_no',
        'company_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
