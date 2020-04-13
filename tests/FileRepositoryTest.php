<?php

namespace xfudox\File\Tests;

use Illuminate\Support\Facades\Storage;
use xfudox\File\Models\File;
use xfudox\File\Tests\TestCase;

class FileRepositoryTest extends TestCase
{
    
    public function testCreateFromUploadedFileToDefaultLocation()
    {
        $file = $this->test_file;

        $this->assertIsObject($file);
        $this->assertInstanceOf(File::class, $file);

        Storage::disk($file->disk)->assertExists($file->fullname);

        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
        $this->assertEquals(Storage::disk($file->disk)->mimeType($file->fullname), $file->mime);
        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
    }

    /** @depends testCreateFromUploadedFileToDefaultLocation */
    public function testFileLocationIsDefaultDiskRoot()
    {
        $file = $this->test_file;
        
        $this->assertEquals(static::DEFAULT_DISK, $file->disk);
        $this->assertEquals('', $file->path);
    }

    /** @depends testCreateFromUploadedFileToDefaultLocation */
    public function testGetFileUrl()
    {
        $file = $this->test_file;
        
        $expected_url   = Storage::disk($file->disk)->url($file->fullname);
        $actual_url     = $this->test_repository->getFileUrl($file);

        $this->assertEquals($expected_url, $actual_url);
    }

    /** @depends testCreateFromUploadedFileToDefaultLocation */
    public function testFileExistsOnStorage()
    {
        $file = $this->test_file;
        
        $actual     = $this->test_repository->exists($file);
        $expected   = Storage::disk($file->disk)->exists($file->fullname);
        $this->assertTrue($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testFileDontExistsOnStorage()
    {
        $missing_file = File::create([
            'name'      => 'missing_image.png',
            'extension' => 'png',
            'mime'      => 'image/png',
            'disk'      => static::DEFAULT_DISK,
            'path'      => '/',
            'size'      => 512,
        ]);
        $actual     = $this->test_repository->exists($missing_file);
        $expected   = Storage::disk($missing_file->disk)->exists($missing_file->fullname);
        $this->assertFalse($expected);
        $this->assertEquals($expected, $actual);
    }

    /** @depends testCreateFromUploadedFileToDefaultLocation */
    public function testMoveFileOnSameDisk()
    {
        $file = $this->test_file;
        
        $source      = $file->fullname;
        $path        = 'dir';
        $destination = "{$path}/{$file->name}";

        $this->test_repository->moveFile($file, $destination);

        $this->assertEquals($path, $file->path);
        $this->assertEquals($destination, $file->fullname);
        Storage::disk($file->disk)->assertExists($destination);
        Storage::disk($file->disk)->assertMissing($source);
    }

    /** @depends testMoveFileOnSameDisk */
    public function testMoveFileOnDifferentDisks()
    {
        $file = $this->test_file;
        
        $disk        = static::SECOND_DISK;
        $path        = 'directory';
        $destination = "{$path}/{$file->name}";

        $this->test_repository->moveFile($file, "{$disk}::" . $destination);

        $this->assertEquals($disk, $file->disk);
        $this->assertEquals($path, $file->path);
        $this->assertEquals($destination, $file->fullname);
        Storage::disk($file->disk)->assertExists($destination);
    }

}
