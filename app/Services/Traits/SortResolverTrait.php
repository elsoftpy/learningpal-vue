<?php

namespace App\Services\Traits;

use Illuminate\Http\Request;

trait SortResolverTrait
{
    protected function resolveSort(
        Request $request,
        array $allowedSortFields,
        string $defaultSortField,
        string $defaultSortOrder = 'asc'
    ): array {
        $sortField = $request->string('sort_field')->toString();
        $sortOrder = strtolower($request->string('sort_order', $defaultSortOrder)->toString());

        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = $defaultSortField;
        }

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = $defaultSortOrder;
        }

        return [$sortField, $sortOrder];
    }
}