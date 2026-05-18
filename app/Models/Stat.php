<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
