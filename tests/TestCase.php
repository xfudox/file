<?php

namespace xfudox\File\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use xfudox\File\FileServiceProvider;
use xfudox\File\Models\File;
use xfudox\File\Repositories\FileRepository;

class TestCase extends TestbenchTestCase
{

	const DEFAULT_DISK	= 'default_disk';
	const SECOND_DISK	= 'second_disk';

	protected $test_file;
    protected $test_repository;
	
	protected function getPackageProviders($app)
	{
		return [FileServiceProvider::class];
	}

	protected function getEnvironmentSetUp($app)
	{
		$this->getDatabaseConfig($app);
		$this->getStorageConfig($app);
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->runDefaultMigrations();
		$this->setUpStorage();
		$this->createTestRepository();
		$this->createTestFile();
	}

	private function runDefaultMigrations()
	{
		$this->artisan('migrate', 
			['--database' => 'testbench'])->run();
	}

	private function getDatabaseConfig($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'	=> 'sqlite',
			'database' 	=> ':memory:',
			'prefix'	=> '',
		]);
	}

	private function getStorageConfig($app)
	{
		$app['config']->set('filesystems.default', static::DEFAULT_DISK);
	}

	private function setUpStorage()
	{
		Storage::fake(static::DEFAULT_DISK);
		Storage::fake(static::SECOND_DISK);
	}

	private function createTestRepository()
	{
        $this->test_repository = App::make(FileRepository::class);
	}

	private function createTestFile()
	{
		$uploaded_file = UploadedFile::fake()->image('uploaded_image.png');
		$uploaded_file->storeAs('/', $uploaded_file->getClientOriginalName(), ['disk' => static::DEFAULT_DISK]);
		
		$this->test_file = File::create([
            'name'      => $uploaded_file->getClientOriginalName(),
            'extension' => $uploaded_file->getClientOriginalExtension(),
            'mime'      => $uploaded_file->getClientMimeType(),
            'size'      => $uploaded_file->getSize(),
            'disk'      => static::DEFAULT_DISK,
            'path'      => '/',
        ]);
	}
}