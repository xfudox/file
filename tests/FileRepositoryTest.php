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
    
    public function testCreateFromUploadedFile()
    {
        $uploaded_file = UploadedFile::fake()->image('uploaded_image.png');

        $file = App::make(FileRepository::class)->createFromUploadedFile($uploaded_file);

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
        $actual_url     = App::make(FileRepository::class)->getFileUrl($file);

        $this->assertEquals($expected_url, $actual_url);
    }

    /** @depends testCreateFromUploadedFile */
    public function testMoveFileOnSameDisk(File $file)
    {
        $source      = $file->fullname;
        $path        = 'dir';
        $destination = "{$path}/{$file->name}";

        App::make(FileRepository::class)->moveFile($file, $destination);

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

        App::make(FileRepository::class)->moveFile($file, "{$disk}::" . $destination);

        $this->assertEquals($disk, $file->disk);
        $this->assertEquals($path, $file->path);
        $this->assertEquals($destination, $file->fullname);
        Storage::disk($file->disk)->assertExists($destination);

        return $file;
    }

}
