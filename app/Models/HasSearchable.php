<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * HasSearchable
 */
trait HasSearchable
{
    public function scopeSearch(Builder $query, $search, $columns)
    {
        return $query->when(
            $search,
            fn ($query, $search) => $query->where(function ($query) use ($search, $columns) {
                foreach ($columns as $column) {
                    $query->orWhereRaw("CAST({$column} AS CHAR) LIKE ?", "%{$search}%");
                }
                return $query;
            })
        );
    }
}
