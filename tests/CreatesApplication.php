<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $database = $app['config']->get('database.connections.mysql.database', '');
        if (! $app->environment('testing') || ! preg_match('/(_qa|_test|testing)$/', $database)) {
            throw new \RuntimeException('Tests require APP_ENV=testing and a disposable MySQL database ending in _qa, _test or testing. Never run tests against production.');
        }

        return $app;
    }
}
