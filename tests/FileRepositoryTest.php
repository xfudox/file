<?php

namespace xfudox\File\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use xfudox\File\Events\FileIsCreated;
use xfudox\File\Events\FileIsMoved;
use xfudox\File\Models\File;
use xfudox\File\Tests\TestCase;

class FileRepositoryTest extends TestCase
{
    
    public function testCreateFromUploadedFileToDefaultLocation()
    {
        $this->expectsEvents(FileIsCreated::class);

        $uploaded_file  = UploadedFile::fake()->image('uploaded_image.png');
        $file            = $this->test_repository->createFromUploadedFile($uploaded_file);

        $this->assertIsObject($file);
        $this->assertInstanceOf(File::class, $file);

        // check file existence on storage
        Storage::disk($file->disk)->assertExists($file->fullname);

        // check file attributes
        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
        $this->assertEquals(Storage::disk($file->disk)->mimeType($file->fullname), $file->mime);
        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
        
        // check file default  location
        $this->assertEquals(static::DEFAULT_DISK, $file->disk);
        $this->assertEquals('', $file->path);
    }
    
    public function testCreateFromUploadedFileToDifferentLocation()
    {
        $this->expectsEvents(FileIsCreated::class);

        $uploaded_file  = UploadedFile::fake()->image('uploaded_image.png');
        $location = static::SECOND_DISK . '::directory';
        $file            = $this->test_repository->createFromUploadedFile($uploaded_file, $location);

        $this->assertIsObject($file);
        $this->assertInstanceOf(File::class, $file);

        // check file existence on storage
        Storage::disk($file->disk)->assertExists($file->fullname);

        // check file attributes
        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
        $this->assertEquals(Storage::disk($file->disk)->mimeType($file->fullname), $file->mime);
        $this->assertEquals(Storage::disk($file->disk)->size($file->fullname), $file->size);
        
        // check file default  location
        $this->assertEquals(static::SECOND_DISK, $file->disk);
        $this->assertEquals('directory', $file->path);
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
        $this->expectsEvents(FileIsMoved::class);

        $file = $this->test_file;
        
        $source      = $file->fullname;
        $path        = 'new_directory';
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
        $this->expectsEvents(FileIsMoved::class);

        $file = $this->test_file;

        $original_fullname  = $file->fullname;
        $original_disk      = $file->disk;
        
        $destination_disk       = static::SECOND_DISK;
        $destination_path       = 'directory';
        $destination_fullname   = "{$destination_path}/{$file->name}";

        $this->test_repository->moveFile($file, "{$destination_disk}::{$destination_fullname}");

        $this->assertEquals($destination_disk, $file->disk);
        $this->assertEquals($destination_path, $file->path);
        $this->assertEquals($destination_fullname , $file->fullname);
        Storage::disk($destination_disk)->assertExists($destination_fullname);
        Storage::disk($original_disk)->assertMissing($original_fullname);
    }

}
