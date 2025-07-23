<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchArea extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    public function scopeActive(Builder $query)
    {
        return $query->whereActive(true);
    }
}
