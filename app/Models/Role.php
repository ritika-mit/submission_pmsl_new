<?php

namespace App\Models;

use App\Enums\Section;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as Base;

class Role extends Base
{
    use HasHashId, HasSearchable;

    protected $hidden = [
        'guard_name',
    ];

    protected $casts = [
        'section' => Section::class,
        'default' => 'boolean',
    ];

    public function scopeActive(Builder $query)
    {
        return $query;
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('default', true);
    }
}
