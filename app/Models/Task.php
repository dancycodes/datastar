<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'due_date', 'is_completed'];

    protected $casts = [
        'due_date' => 'datetime',
        'is_completed' => 'boolean',
    ];
}
