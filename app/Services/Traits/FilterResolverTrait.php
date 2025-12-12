<?php

namespace App\Services\Traits;

trait FilterResolverTrait
{
    protected function resolveFilters($filters): array
    {
        if (is_array($filters)) {
            return $filters;
        }

        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}