<?php

namespace App\Providers;

use App\Services\Payments\FakeGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\RazorpayGateway;
use App\Services\Payments\StripeGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function ($app) {
            $config = $app['config'];

            return match ($config->get('services.payment_driver')) {
                'stripe' => new StripeGateway(
                    $config->get('services.stripe.secret', ''),
                    $config->get('services.stripe.webhook_secret', ''),
                ),
                'razorpay' => new RazorpayGateway(
                    $config->get('services.razorpay.key', ''),
                    $config->get('services.razorpay.secret', ''),
                ),
                default => new FakeGateway(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn ($user) => $user->isAdmin());
    }
}
