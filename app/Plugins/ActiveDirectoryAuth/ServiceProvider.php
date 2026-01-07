<?php

namespace App\Plugins\ActiveDirectoryAuth;

use App\Plugins\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        parent::register('ActiveDirectoryAuth');

        $this->loadViewsFrom(app_path('Plugins/ActiveDirectoryAuth/views'), 'active-directory-auth');

        $this->mergeConfigFrom(
            app_path('Plugins/ActiveDirectoryAuth/Config/config.php'),
            'active_directory_auth'
        );

        $this->app->singleton(AdAuthenticator::class, function ($app) {
            if (class_exists(\Adldap\Adldap::class)) {
                return new AdAuthenticator($app->make(\Adldap\Adldap::class));
            }

            return new AdAuthenticator();
        });
    }
}
