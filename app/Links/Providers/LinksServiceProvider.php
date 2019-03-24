<?php

namespace App\Links\Providers;

use App\Links\CompressedLink;
use App\Links\CompressedLinkInterface;
use App\Links\Factories\CompressedLinkFactory;
use App\Links\Factories\CompressedLinkFactoryInterface;
use App\Links\Repositories\CompressedLinkRepository;
use App\Links\Repositories\CompressedLinkRepositoryInterface;
use App\Links\Services\CompressedLinkService;
use App\Links\Services\CompressedLinkServiceInterface;
use Illuminate\Support\ServiceProvider;

class LinksServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CompressedLinkServiceInterface::class, CompressedLinkService::class);
        $this->app->bind(CompressedLinkRepositoryInterface::class, CompressedLinkRepository::class);
        $this->app->bind(CompressedLinkInterface::class, CompressedLink::class);
        $this->app->bind(CompressedLinkFactoryInterface::class, CompressedLinkFactory::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

}
