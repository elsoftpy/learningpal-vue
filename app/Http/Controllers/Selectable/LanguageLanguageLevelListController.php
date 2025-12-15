<?php

namespace App\Http\Controllers\Selectable;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\LanguageLevel;
use Illuminate\Http\Request;

class LanguageLanguageLevelListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $languageId = $request->language_id;

        return LanguageLevel::query()
            ->select('id', 'description as name')
            ->where('language_id', $languageId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get();
    }
}
