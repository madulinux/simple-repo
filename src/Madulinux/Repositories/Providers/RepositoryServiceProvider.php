<?php
namespace Madulinux\Repositories\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Madulinux\Repositories\Console\Commands\Creators\RepositoryCreator;
use Madulinux\Repositories\Console\Commands\Creators\CriteriaCreator;
use Madulinux\Repositories\Console\Commands\MakeRepositoryCommand;
use Madulinux\Repositories\Console\Commands\MakeCriteriaCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        $this->registerBindings();
        
        $this->commands([
            MakeRepositoryCommand::class,
            MakeCriteriaCommand::class
        ]);
    }

    public function boot()
    {
        $config_path = __DIR__ . '/../../../config/repositories.php';
        $this->publishes(
            [$config_path => config_path('repositories.php')],
            'repositories'
        );
        $custom_eloquent_path = __DIR__ . '/../../../config/CustomEloquentProvider.php';
        $this->publishes(
            [$custom_eloquent_path => base_path('/app/Providers/CustomEloquentProvider.php')],
            'repositories'
        );
    }

    public function registerBindings()
    {
        $this->app->instance('FileSystem', new Filesystem());

        $this->app->bind('Composer', function($app) {
            return new Composer($app['FileSystem']);
        });

        $this->app->singleton('RepositoryCreator', function ($app) {
            return new RepositoryCreator($app['FileSystem']);
        });

        $this->app->singleton('CriteriaCreator', function ($app) {
            return new CriteriaCreator($app['FileSystem']);
        });
    }
}
