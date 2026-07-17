<?php

namespace App\Services\Utilities;

use Illuminate\Database\QueryException;

class DatabaseErrorMessageResolver
{
    public static function userMessageFor(QueryException $exception): ?string
    {
        $errorInfo = $exception->errorInfo;

        $sqlState = isset($errorInfo[0]) ? (string) $errorInfo[0] : '';
        $driverCode = isset($errorInfo[1]) ? (int) $errorInfo[1] : 0;
        $driverMessage = isset($errorInfo[2]) ? (string) $errorInfo[2] : '';

        if ($sqlState !== '22001' || $driverCode !== 1406) {
            return null;
        }

        if (str_contains($driverMessage, "column 'links'")) {
            return __('One or more links are too long. Please use shorter links or remove tracking parameters and try again.');
        }

        return __('Some text in the form is too long. Please shorten it and try again.');
    }
}
