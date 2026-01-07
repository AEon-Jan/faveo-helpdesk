<?php

namespace App\Plugins\ActiveDirectoryAuth;

use App\Plugins\ServiceProvider as BaseServiceProvider;
use App\Plugins\ActiveDirectoryAuth\SettingsRepository;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(app_path('Plugins/ActiveDirectoryAuth/database/migrations'));
    }

    public function register()
    {
        parent::register('ActiveDirectoryAuth');

        $this->loadViewsFrom(app_path('Plugins/ActiveDirectoryAuth/views'), 'active-directory-auth');

        $this->mergeConfigFrom(
            app_path('Plugins/ActiveDirectoryAuth/Config/config.php'),
            'active_directory_auth'
        );

        $this->app->singleton(SettingsRepository::class, function () {
            return new SettingsRepository();
        });

        $this->app->singleton(AdAuthenticator::class, function ($app) {
            if (class_exists(\Adldap\Adldap::class)) {
                return new AdAuthenticator(
                    $app->make(\Adldap\Adldap::class),
                    $app->make(SettingsRepository::class)
                );
            }

            return new AdAuthenticator(null, $app->make(SettingsRepository::class));
        });
    }
}
