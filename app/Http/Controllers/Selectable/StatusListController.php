<?php

namespace App\Http\Controllers\Selectable;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatusListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return array_map(
            fn($value) => ['value' => $value, 'name' => strtoupper(__($value))],
            StatusEnum::values()
        );
    }
}
