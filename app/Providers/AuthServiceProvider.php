<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\CarbonInterval;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Model => Policy mappings
    ];


    public function boot(): void
    {
        $this->registerPolicies();

        // Passport::routes(); // This registers the routes for issuing tokens
    }

    // public function boot(): void
    // {
    //     $this->registerPolicies();
    //     // Passport::routes();


        // Token expiration times
    //     Passport::tokensExpireIn(CarbonInterval::days(15));
    //     Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    //     Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
    // }
}
