<?php

namespace xfudox\File;

use Illuminate\Support\ServiceProvider;
use xfudox\File\Repositories\Eloquent\EloquentFileRepository;
use xfudox\File\Repositories\FileRepository;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            FileRepository::class,
            EloquentFileRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'file');
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
    }
}
