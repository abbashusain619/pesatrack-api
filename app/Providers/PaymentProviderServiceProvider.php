<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PaymentProviders\AccountProviderInterface;
use App\Services\PaymentProviders\MockProvider;

class PaymentProviderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AccountProviderInterface::class, function ($app) {
            $provider = config('payment.default_provider', 'mock');
            switch ($provider) {
                case 'azampay':
                    // Later: return new AzamPayProvider();
                    // For now fallback to mock
                    return new MockProvider();
                default:
                    return new MockProvider();
            }
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}