<?php

namespace xfudox\File\Tests;

use xfudox\File\Models\File;
use xfudox\File\Tests\TestCase;

class FileTest extends TestCase
{
    
    public function testCanCreate()
    {
        $file = File::create([
            'name'      => static::DEFAULT_NAME,
            'extension' => static::DEFAULT_EXTENSION,
            'mime'      => static::DEFAULT_MIME,
            'disk'      => static::DEFAULT_DISK,
            'path'      => static::DEFAULT_PATH,
            'size'      => static::DEFAULT_SIZE,
        ]);

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
        $has_updated = $file->update([
            'name'      => 'new_name.pdf',
            'extension' => 'pdf',
            'mime'      => 'application/pdf',
            'disk'      => 'remote',
            'path'      => 'new/path',
            'size'      => 2048,
        ]);

        $this->assertTrue($has_updated);
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
}
