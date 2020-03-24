<?php

namespace xfudox\File\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use xfudox\File\FileServiceProvider;

abstract class TestCase extends TestbenchTestCase
{
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return lasselehtinen\MyPackage\MyPackageServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [FileServiceProvider::class];
    }
}
