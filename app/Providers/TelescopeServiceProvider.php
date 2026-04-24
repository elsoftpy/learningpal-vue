<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                   $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user) {
            $allowedEmails = collect(explode(',', (string) env('TELESCOPE_ALLOWED_EMAILS', '')))
                ->map(fn (string $email): string => strtolower(trim($email)))
                ->filter()
                ->values();

            $allowedDomains = collect(explode(',', (string) env('TELESCOPE_ALLOWED_DOMAINS', '')))
                ->map(fn (string $domain): string => ltrim(strtolower(trim($domain)), '@'))
                ->filter()
                ->values();

            if ($allowedEmails->isEmpty() && $allowedDomains->isEmpty()) {
                return false;
            }

            $userEmail = strtolower((string) $user->email);

            if ($allowedEmails->contains($userEmail)) {
                return true;
            }

            return $allowedDomains->contains(
                fn (string $domain): bool => Str::endsWith($userEmail, '@'.$domain)
            );
        });
    }
}
