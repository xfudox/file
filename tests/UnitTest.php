<?php

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use xfudox\File\Models\File;

class UnitTest extends TestbenchTestCase
{

    const DEFAULT_NAME      = 'default_file.png';
    const DEFAULT_EXTENSION = 'png';
    const DEFAULT_MIME      = 'image/png';
    const DEFAULT_DISK      = 'local';
    const DEFAULT_PATH      = 'path';
    const DEFAULT_SIZE      = '1024';
    
    public function testCanCreate()
    {
        $file = $this->createDefaultFile();

        $this->assertIsObject($file);
        $this->assertInstanceOf(File::class, $file);

        return $file;
    }

    /** @depends testCanCreate */
    public function testCheckAttributes(File $file)
    {
        $this->assertEquals(static::DEFAULT_NAME, $file->name);
        $this->assertEquals(static::DEFAULT_EXTENSION, $file->extension);
        $this->assertEquals(static::DEFAULT_MIME, $file->mime);
        $this->assertEquals(static::DEFAULT_DISK, $file->disk);
        $this->assertEquals(static::DEFAULT_PATH, $file->path);
        $this->assertEquals(static::DEFAULT_SIZE, $file->size);

        return $file;
    }

    /** @depends testCheckAttributes */
    public function testCheckAppendedAttributes(File $file)
    {
        $fullname = static::DEFAULT_PATH . '/' . static::DEFAULT_NAME;
        $this->assertEquals($fullname, $file->fullname);
        // TODO: check url
        // TODO: check exists

        return $file;
    }

    /** @depends testCheckAttributes */
    public function testCanUpdate(File $file)
    {
        $file->update([
            'name'      => 'new_name.pdf',
            'extension' => 'pdf',
            'mime'      => 'application/pdf',
            'disk'      => 'remote',
            'path'      => 'new/path',
            'size'      => '2048',
        ]);

        $this->assertEquals('new_name.pdf', $file->name);
        $this->assertEquals('pdf', $file->extension);
        $this->assertEquals('application/pdf', $file->mime);
        $this->assertEquals('remote', $file->disk);
        $this->assertEquals('new/path', $file->path);
        $this->assertEquals('2048', $file->size);

        return $file;
    }

    /** @depends testCheckAttributes */
    public function testCheckMutators(File $file)
    {
        $file->update([
            'path' => '/'
        ]);

        $this->assertEquals('', $file->path);

        return $file;
    }

    /** @depends testCheckAttributes */
    public function testCheckAccessors(File $file)
    {
        return $file;
    }

    private function createDefaultFile() : File
    {
        return new File([
            'name'      => static::DEFAULT_NAME,
            'extension' => static::DEFAULT_EXTENSION,
            'mime'      => static::DEFAULT_MIME,
            'disk'      => static::DEFAULT_DISK,
            'path'      => static::DEFAULT_PATH,
            'size'      => static::DEFAULT_SIZE,
        ]);
    }
}
