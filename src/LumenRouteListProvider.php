<?php

namespace Sayeed\LumenRouteList;

use Illuminate\Support\ServiceProvider;

class LumenRouteListProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \Sayeed\LumenRouteList\RouteListCommand::class,
        ]);
    }
}
