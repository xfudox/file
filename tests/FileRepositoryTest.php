<?php

namespace xfudox\File\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use xfudox\File\Models\File;
use xfudox\File\Repositories\FileRepository;
use xfudox\File\Tests\TestCase;

class FileRepositoryTest extends TestCase
{

    private $repository;

    public function setUp() : void
    {
        parent::setUp();
        $this->repository = App::make(FileRepository::class);
    }
    
    public function testCreateFromUploadedFile()
    {
        $uploaded_file = UploadedFile::fake()->image('uploaded_image.png');

        $file = $this->repository->createFromUploadedFile($uploaded_file);

        $this->assertIsObject($file);
        $this->assertInstanceOf(File::class, $file);

        Storage::disk($file->disk)->assertExists($file->fullname);

        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
        $this->assertEquals(Storage::disk($file->disk)->mimeType($file->fullname), $file->mime);
        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);

        return $file;
    }

    /** @depends testCreateFromUploadedFile */
    public function testGetFileUrl(File $file)
    {
        $expected_url   = Storage::disk($file->disk)->url($file->fullname);
        $actual_url     = $this->repository->getFileUrl($file);

        $this->assertEquals($expected_url, $actual_url);
    }

    /** @depends testCreateFromUploadedFile */
    public function testExists(File $file)
    {
        $actual     = $this->repository->exists($file);
        $expected   = Storage::disk($file->disk)->exists($file->fullname);
        $this->assertTrue($expected);
        $this->assertEquals($expected, $actual);

        $missing_file = File::create([
            'name'      => 'missing_image.png',
            'extension' => 'png',
            'mime'      => 'image/png',
            'disk'      => static::DEFAULT_DISK,
            'path'      => '/',
            'size'      => 512,
        ]);
        $actual     = $this->repository->exists($missing_file);
        $expected   = Storage::disk($missing_file->disk)->exists($missing_file->fullname);
        $this->assertFalse($expected);
        $this->assertEquals($expected, $actual);

        return $file;
    }

    /** @depends testCreateFromUploadedFile */
    public function testMoveFileOnSameDisk(File $file)
    {
        $source      = $file->fullname;
        $path        = 'dir';
        $destination = "{$path}/{$file->name}";

        $this->repository->moveFile($file, $destination);

        $this->assertEquals($path, $file->path);
        $this->assertEquals($destination, $file->fullname);
        Storage::disk($file->disk)->assertExists($destination);
        Storage::disk($file->disk)->assertMissing($source);

        return $file;
    }

    /** @depends testMoveFileOnSameDisk */
    public function testMoveFileOnDifferentDisks(File $file)
    {
        $disk        = static::SECOND_DISK;
        $path        = 'directory';
        $destination = "{$path}/{$file->name}";

        $this->repository->moveFile($file, "{$disk}::" . $destination);

        $this->assertEquals($disk, $file->disk);
        $this->assertEquals($path, $file->path);
        $this->assertEquals($destination, $file->fullname);
        Storage::disk($file->disk)->assertExists($destination);

        return $file;
    }

}
