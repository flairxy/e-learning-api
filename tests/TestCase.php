<?php

namespace Tests;

use Tests\Traits\ConsumesApi;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    use ConsumesApi;
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
