<?php

namespace xfudox\File\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use xfudox\File\FileServiceProvider;

class TestCase extends TestbenchTestCase
{
	protected function getPackageProviders($app)
	{
		return [FileServiceProvider::class];
	}

	protected function getEnvironmentSetUp($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->artisan('migrate', 
					['--database' => 'testbench'])->run();    
	}
}