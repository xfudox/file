<?php

namespace xfudox\File\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use xfudox\File\Events\FileIsCreated;
use xfudox\File\Models\File;
use xfudox\File\Tests\TestCase;

class FileRepositoryTest extends TestCase
{
    /**
     * Test that from string are extracted correctly expected filepath information.
     *
     * @return void
     */
    public function testGetDataFromDestination()
    {
        $expected_results = [
            'path/to/file.png' => [
                'path'      => 'path/to/',
                'name'      => 'file.png',
                'extension' => 'png'
            ],
            'path/to/file' => [
                'path'      => 'path/to/',
                'name'      => 'file',
                'extension' => null
            ],
            'path/to/' => [
                'path'      => 'path/to/',
                'name'      => null,
                'extension' => null
            ],
            'file' => [
                'path'      => '/',
                'name'      => 'file',
                'extension' => null
            ],
            'path/to/.hidden_file' => [
                'path'      => 'path/to/',
                'name'      => '.hidden_file',
                'extension' => null
            ],
            '/' => [
                'path'      => '/',
                'name'      => null,
                'extension' => null
            ],
            '' => [
                'path'      => '/',
                'name'      => null,
                'extension' => null
            ],
            '/path/to/file.png' => [
                'path'      => 'path/to/',
                'name'      => 'file.png',
                'extension' => 'png'
            ],
        ];

        foreach($expected_results as $input => $output){
            $actual_results = $this->test_repository->getDataFromDestination($input);

            foreach($output as $value => $expected){
                if(null == $expected){
                    $this->assertNull(
                        $actual_results[$value],
                        "Failed asserting that expected null {$value} equals '{$actual_results[$value]}' for input '{$input}'"
                    );
                }
                else{
                    $this->assertEquals(
                        $expected,
                        $actual_results[$value],
                        "Failed asserting that expected {$value} '{$expected}' equals '{$actual_results[$value]}' for input '{$input}'"
                    );
                }

            }
        }
    }

    /**
     * Checks:
     *  1 FileIsCreted event is fired
     *  2 return a File instance
     *  3 returned File instance has same name, extension and size of given uploaded file
     *  4 returned File instance refers default disk
     *  5 returned File instance path is root
     *  6 file actually exists on default disk
     *
     * @return void
     */
    public function testFileCreationWithDefaultArguements()
    {
        $uploaded_file      = UploadedFile::fake()->image('uploaded_image.png');
        $original_extension = $uploaded_file->getClientOriginalExtension();
        $original_name      = $uploaded_file->getClientOriginalName();
        $original_size      = $uploaded_file->getSize();
        $default_disk       = config('filesystems.default');
        
        /* 1 */ $this->expectsEvents(FileIsCreated::class);

        $file = $this->test_repository->createFromUploadedFile($uploaded_file);

        /* 2 */ $this->assertInstanceOf(File::class, $file);
        /* 3 */ $this->assertEquals($original_name, $file->name);
        /* 3 */ $this->assertEquals($original_extension, $file->extension);
        /* 3 */ $this->assertEquals($original_size, $file->size);
        /* 4 */ $this->assertEquals($default_disk, $file->disk);
        /* 5 */ $this->assertEquals('/', $file->path);
        /* 6 */ Storage::disk($default_disk)->assertExists($file->fullname);
    }

    /**
     * Checks:
     *  1 FileIsCreted event is fired
     *  2 return a File instance
     *  3 returned File instance has size of given uploaded file
     *  4 returned File instance has given name and extension
     *  5 returned File instance path disk is root
     *  6 returned File instance refers default disk
     *  7 file actually exists on default disk
     *
     * @return void
     */
    public function testFileCreationWithNewName()
    {
        $uploaded_file      = UploadedFile::fake()->image('uploaded_image.png');
        $new_name           = 'new_name.png';
        $new_extension      = 'png';
        $original_size      = $uploaded_file->getSize();
        $default_disk       = config('filesystems.default');
        
        /* 1 */ $this->expectsEvents(FileIsCreated::class);

        $file = $this->test_repository->createFromUploadedFile($uploaded_file, $new_name);

        /* 2 */ $this->assertInstanceOf(File::class, $file);
        /* 3 */ $this->assertEquals($original_size, $file->size);
        /* 4 */ $this->assertEquals($new_name, $file->name);
        /* 4 */ $this->assertEquals($new_extension, $file->extension);
        /* 5 */ $this->assertEquals('/', $file->path);
        /* 6 */ $this->assertEquals($default_disk, $file->disk);
        /* 7 */ Storage::disk($default_disk)->assertExists($file->fullname);
    }

    /**
     * Checks:
     *  1 FileIsCreted event is fired
     *  2 return a File instance
     *  3 returned File instance has same name, extension and size of given uploaded file
     *  4 returned File instance path is given one
     *  5 returned File instance refers default disk
     *  6 file actually exists on default disk at given path
     *
     * @return void
     */
    public function testFileCreationAtGivenPath()
    {
        $uploaded_file      = UploadedFile::fake()->image('uploaded_image.png');
        $original_extension = $uploaded_file->getClientOriginalExtension();
        $original_name      = $uploaded_file->getClientOriginalName();
        $original_size      = $uploaded_file->getSize();
        $path               = 'directory/';
        $default_disk       = config('filesystems.default');
        
        /* 1 */ $this->expectsEvents(FileIsCreated::class);

        $file = $this->test_repository->createFromUploadedFile($uploaded_file, $path);

        /* 2 */ $this->assertInstanceOf(File::class, $file);
        /* 3 */ $this->assertEquals($original_name, $file->name);
        /* 3 */ $this->assertEquals($original_extension, $file->extension);
        /* 3 */ $this->assertEquals($original_size, $file->size);
        /* 4 */ $this->assertEquals($path, $file->path);
        /* 5 */ $this->assertEquals($default_disk, $file->disk);
        /* 6 */ Storage::disk($default_disk)->assertExists($file->fullname);
    }

    /**
     * Checks:
     *  1 FileIsCreted event is fired
     *  2 return a File instance
     *  3 returned File instance has size of given uploaded file
     *  4 returned File instance has given name and extension
     *  5 returned File instance path disk is root
     *  6 returned File instance refers given disk
     *  7 file actually exists on given disk
     *
     * @return void
     */
    public function testFileCreationWithNewNameOnGivenDisk()
    {
        $uploaded_file      = UploadedFile::fake()->image('uploaded_image.png');
        $new_name           = 'new_name.png';
        $new_extension      = 'png';
        $original_size      = $uploaded_file->getSize();
        $disk               = static::SECOND_DISK;
        
        /* 1 */ $this->expectsEvents(FileIsCreated::class);

        $file = $this->test_repository->createFromUploadedFile($uploaded_file, $new_name, $disk);

        /* 2 */ $this->assertInstanceOf(File::class, $file);
        /* 3 */ $this->assertEquals($original_size, $file->size);
        /* 4 */ $this->assertEquals($new_name, $file->name);
        /* 4 */ $this->assertEquals($new_extension, $file->extension);
        /* 5 */ $this->assertEquals('/', $file->path);
        /* 6 */ $this->assertEquals($disk, $file->disk);
        /* 7 */ Storage::disk($disk)->assertExists($file->fullname);
    }

    /**
     * Checks:
     *  1 FileIsCreted event is fired
     *  2 return a File instance
     *  3 returned File instance has same name, extension and size of given uploaded file
     *  4 returned File instance path is given one
     *  5 returned File instance refers given disk
     *  6 file actually exists on given disk at given path
     *
     * @return void
     */
    public function testFileCreationAtGivenPathOnGivenDisk()
    {
        $uploaded_file      = UploadedFile::fake()->image('uploaded_image.png');
        $original_extension = $uploaded_file->getClientOriginalExtension();
        $original_name      = $uploaded_file->getClientOriginalName();
        $original_size      = $uploaded_file->getSize();
        $path               = 'directory/';
        $disk               = static::SECOND_DISK;
        
        /* 1 */ $this->expectsEvents(FileIsCreated::class);

        $file = $this->test_repository->createFromUploadedFile($uploaded_file, $path, $disk);

        /* 2 */ $this->assertInstanceOf(File::class, $file);
        /* 3 */ $this->assertEquals($original_name, $file->name);
        /* 3 */ $this->assertEquals($original_extension, $file->extension);
        /* 3 */ $this->assertEquals($original_size, $file->size);
        /* 4 */ $this->assertEquals($path, $file->path);
        /* 5 */ $this->assertEquals($disk, $file->disk);
        /* 6 */ Storage::disk($disk)->assertExists($file->fullname);
    }

    /**
     * Checks:
     *  1 url generated for given File equals the one generated with the Storage facade using File's own disk, path and name.
     *
     * @return void
     */
    public function testGetFileUrl()
    {
        $file = $this->test_file;
        
        $expected_url   = Storage::disk($file->disk)->url($file->fullname);
        $actual_url     = $this->test_repository->getFileUrl($file);

        /* 1 */ $this->assertEquals($expected_url, $actual_url);
    }

    /**
     * Checks:
     *  1 given File instance refer an existing file on File's disk at File path and name.
     *
     * @return void
     */
    public function testFileExistsOnStorage()
    {
        $file = $this->test_file;
        
        /* 1 */ Storage::disk($file->disk)->assertExists($file->fullname);
    }

    /**
     * Checks:
     *  1 given File instance don't refer an existing file on File's disk at File path and name.
     *
     * @return void
     */
    public function testFileDontExistsOnStorage()
    {
        $missing_file = File::create([
            'name'      => 'missing_image.png',
            'extension' => 'png',
            'disk'      => static::DEFAULT_DISK,
            'path'      => '/',
            'size'      => 512,
        ]);
        
        /* 1 */ Storage::disk($missing_file->disk)->assertMissing($missing_file->fullname);
    }

    /**
     * Check returned file size in every possible format.
     * 
     * Available measure unit are:
     *  • b/bytes
     *  • kb/kilobytes
     *  • mb/megabytes
     *  • gb/gigabytes
     * 
     * Available representation are:
     *  • binary
     *  • decimal
     * 
     * Total possible combinations: 16
     * Total representations: 8
     *
     * @return void
     */
    public function testGetFileSizeInEveryPossibleFormat()
    {
        $file = $this->test_file;

        $data = [
            'b' => [
                'binary'    => $file->size,
                'decimal'   => $file->size,
            ], 
            'bytes' => [
                'binary'    => $file->size,
                'decimal'   => $file->size,
            ],
            'kb' => [
                'binary'    => $file->size / 1024,
                'decimal'   => $file->size / (10 ** 3),
            ], 
            'kilobytes' => [
                'binary'    => $file->size / 1024,
                'decimal'   => $file->size / (10 ** 3),
            ],
            'mb' => [
                'binary'    => $file->size / (1024 ** 2),
                'decimal'   => $file->size / (10 ** 6),
            ], 
            'megabytes' => [
                'binary'    => $file->size / (1024 ** 2),
                'decimal'   => $file->size / (10 ** 6),
            ],
            'gb' => [
                'binary'    => $file->size / (1024 ** 3),
                'decimal'   => $file->size / (10 ** 9),
            ], 
            'gigabytes' => [
                'binary'    => $file->size / (1024 ** 3),
                'decimal'   => $file->size / (10 ** 9),
            ],
        ];

        // test with default arguements
        $this->assertEquals(
            $file->size,
            $this->test_repository->getFileSize($file)
        );

        // test combinations
        foreach($data as $measure_unit => $representations){
            foreach($representations as $representation => $expected){
                $actual = $this->test_repository->getFileSize($file, $measure_unit, $representation);
                $this->assertEquals($expected,$actual);
            }
        }
    }

    /* 
        TODO tests:
            move file on same disk without change name
            move file on same disk changing name
            move file on different disk without change name
            move file on different disk changing name
            get file contents with existing file
            get file contents with non existing file
     */

}
