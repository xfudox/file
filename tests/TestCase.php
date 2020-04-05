<?php

namespace xfudox\File\Tests;

use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use xfudox\File\FileServiceProvider;

class TestCase extends TestbenchTestCase
{

	const DEFAULT_NAME			= 'default_file.png';
	const DEFAULT_EXTENSION		= 'png';
	const DEFAULT_MIME			= 'image/png';
	const DEFAULT_DISK			= 'default_disk';
	const DEFAULT_PATH			= 'path';
	const DEFAULT_SIZE			= 1024;
	const SECOND_DISK			= 'second_disk';
	
	protected function getPackageProviders($app)
	{
		return [FileServiceProvider::class];
	}

	protected function getEnvironmentSetUp($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'	=> 'sqlite',
			'database' 	=> ':memory:',
			'prefix'	=> '',
		]);
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->runDefaultMigrations();
		$this->setUpDisks();
		
	}

	private function runDefaultMigrations()
	{
		$this->artisan('migrate', 
			['--database' => 'testbench'])->run();
	}

	private function setUpDisks()
	{
		Storage::fake(static::DEFAULT_DISK);
		$this->clearDisk(static::DEFAULT_DISK);
		Storage::fake(static::SECOND_DISK);
		$this->clearDisk(static::SECOND_DISK);
	}

	private function clearDisk(?string $disk = null)
	{
		$disk = $disk ?? static::DEFAULT_DISK;
		
		$files = Storage::disk($disk)->allFiles('/');
		foreach($files as $file){
			Storage::disk($disk)->delete($file);
		}
	}
}