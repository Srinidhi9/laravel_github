<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\Passport\RefreshToken;
use App\Models\Passport\AuthCode;
use Laravel\Passport\Client;
use App\Models\Passport\DeviceCode;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Ignore default Passport routes if we want to define custom routes
        Passport::ignoreRoutes();
    }

    public function boot(): void
    {
        // Override Passport models if needed
        // Passport::useTokenModel(Token::class);
        // // Passport::useRefreshTokenModel(RefreshToken::class);
        // Passport::useAuthCodeModel(AuthCode::class);
        Passport::useClientModel(Client::class);
        // Passport::useDeviceCodeModel(DeviceCode::class);
    }
}
