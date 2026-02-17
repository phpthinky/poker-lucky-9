<?php

namespace App\Providers;

use App\Services\DeckService;
use App\Services\GameEngineService;
use App\Services\PayoutService;
use App\Services\RedisStateService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Redis state — singleton so the same instance is reused per request
        $this->app->singleton(RedisStateService::class);

        // DeckService and PayoutService are stateless, bind as singletons for efficiency
        $this->app->singleton(DeckService::class);
        $this->app->singleton(PayoutService::class);

        // GameEngineService depends on the above three — resolved automatically
        $this->app->singleton(GameEngineService::class, function ($app) {
            return new GameEngineService(
                redis:   $app->make(RedisStateService::class),
                deck:    $app->make(DeckService::class),
                payouts: $app->make(PayoutService::class),
            );
        });
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        //
    }
}
