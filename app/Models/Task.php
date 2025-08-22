<?php

namespace App\Models;

use Dancycodes\Todopackage\Traits\HasAnalytics;
use Dancycodes\Todopackage\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use Searchable, HasAnalytics;
    protected $fillable = ['title', 'due_date', 'is_completed', 'user_id'];

    protected $casts = [
        'due_date' => 'datetime',
        'is_completed' => 'boolean',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
