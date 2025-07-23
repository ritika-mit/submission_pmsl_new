<?php

namespace App\Models;

/**
 * HasHashId
 */
trait HasHashId
{
    public function attributesToArray()
    {
        if (!$this->exists) {
            return parent::attributesToArray();
        }

        return array_merge(
            parent::attributesToArray(),
            [$this->getRouteKeyName() => $this->getRouteKey()]
        );
    }

    public function getRouteKey()
    {
        return hash_id_encode(parent::getRouteKey());
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where($field ?? $this->getRouteKeyName(), hash_id_decode($value));
    }
}
