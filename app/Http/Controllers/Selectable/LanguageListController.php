<?php

namespace App\Http\Controllers\Selectable;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return Language::query()
            ->select('id', 'name')
            ->get();
    }
}
