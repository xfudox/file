<?php

namespace xfudox\File\Models;

use Illuminate\Database\Eloquent\Model;
use xfudox\File\Repositories\FileRepository;

class File extends Model
{
    protected $fillable = [
        'name',
        'extension',
        'mime',
        'disk',
        'path',
        'size',
    ];

    protected $appends = [
        'url',
        'fullname',
        'exists',
    ];

    // ACCESSORS AND MUTATORS
    public function setPathAttribute(string $value)
    {
        if(substr($value, -1) == '/'){
            $value = substr($value, 0, -1);
        }

        $this->attributes['path'] = $value;
    }

    // APPENDED ATTRIBUTES ACCESSORS
    public function getFullnameAttribute() : string
    {
        if($this->path == ''){
            return $this->name;
        }
        return $this->path . '/' . $this->name;
    }

    public function getUrlAttribute() : string
    {
        return app(FileRepository::class)->getFileUrl($this);
    }

    public function getExistsAttribute() : bool
    {
        return app(FileRepository::class)->exists($this);
    }

    // ACTIONS
    public function moveTo(string $destination)
    {
        app(FileRepository::class)->moveFile($this, $destination);
        $this->refresh();
    }

    public function rename(string $new_name)
    {
        app(FileRepository::class)->renameFile($this, $new_name);
    }

    public function getContent() : string
    {
        return app(FileRepository::class)->getFileContent($this);
    }

    public function getSizeIn(string $measure_unit = 'bytes', string $conversion = 'binary') : float
    {
        return app(FileRepository::class)->getFileSize($this, $measure_unit, $conversion);
    }

}
