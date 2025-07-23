<?php

if (!function_exists('hash_id_encode')) {
    function hash_id_encode($value): string
    {
        return app(Hashids\Hashids::class)->encode($value);
    }
}

if (!function_exists('hash_id_decode')) {
    function hash_id_decode($value): ?int
    {
        return current(app(Hashids\Hashids::class)->decode($value) ?? []);
    }
}
