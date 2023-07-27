<?php

namespace App\Providers;

use App\Http\Repositories\RedisBatchRepository;
use Illuminate\Bus\BatchFactory;
use Illuminate\Bus\BatchRepository;
use Illuminate\Support\ServiceProvider;

class RedisBatchRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BatchRepository::class, RedisBatchRepository::class);

        $this->app->singleton(RedisBatchRepository::class, function ($app) {
            return new RedisBatchRepository(
                $app->make(BatchFactory::class),
                $app->make('redis')->connection(),
                config('queue.batching.table', 'laravel_batches:')
            );
        });
    }

    public function provides(): array
    {
        return [
            BatchRepository::class,
        ];
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
