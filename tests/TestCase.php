<?php

namespace xfudox\File\Tests;

use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use xfudox\File\FileServiceProvider;

class TestCase extends TestbenchTestCase
{

    const DEFAULT_NAME      = 'default_file.png';
    const DEFAULT_EXTENSION = 'png';
    const DEFAULT_MIME      = 'image/png';
    const DEFAULT_DISK      = 'local';
    const DEFAULT_PATH      = 'path';
	const DEFAULT_SIZE      = 1024;
	
	protected function getPackageProviders($app)
	{
		return [FileServiceProvider::class];
	}

	protected function getEnvironmentSetUp($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   	=> 'sqlite',
			'database' 	=> ':memory:',
			'prefix'   	=> '',
		]);
	}

	public function setUp(): void
	{
		parent::setUp();

		$this->artisan('migrate', 
					['--database' => 'testbench'])->run();
		
		Storage::fake(static::DEFAULT_DISK);
	}
}