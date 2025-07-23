<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermAndCondition extends Model
{
    use HasFactory, HasHashId, HasSearchable;

    protected function termAndCondition(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => html_entity_decode($value),
            set: fn (mixed $value) => htmlentities($value)
        );
    }

    public function scopeActive(Builder $query)
    {
        return $query;
    }
}
