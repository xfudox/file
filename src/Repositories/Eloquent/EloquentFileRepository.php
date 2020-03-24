<?php

namespace xfudox\File\Repositories\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use xfudox\File\Models\File;
use xfudox\File\Repositories\FileRepository;

class EloquentFileRepository implements FileRepository
{
    private $filesystem;

    public function __construct(Filesystem $fs)
    {
        $this->filesystem = $fs;
    }

    /** @inheritDoc */
    public function createFromUploadedFile(UploadedFile $uploaded_file, string $full_path = 'public::/') : File
    {
        list($disk, $path) = explode('::',$full_path);

        $file = File::create([
            'name'      => $uploaded_file->getClientOriginalName(),
            'extension' => $uploaded_file->getClientOriginalExtension(),
            'mime'      => $uploaded_file->getClientMimeType(),
            'size'      => $uploaded_file->getClientSize(),
            'disk'      => $disk,
            'path'      => $path,
        ]);

        $uploaded_file->storeAs($path, $uploaded_file->getClientOriginalName(), ['disk' => $disk]);

        return $file;
    }

    /** @inheritDoc */
    public function getFileUrl(File $file) : string
    {
        if(!$this->exists($file)){
            throw new \InvalidArgumentException("File {$file->fullname} (id: {$file->id}) does not exists");
        }

        return Storage::disk($file->disk)->url($file->fullname);
    }

    /** @inheritDoc */
    public function moveFile(File $file, string $destination)
    {
        if(!$this->exists($file)){
            throw new \InvalidArgumentException("File {$file->fullname} (id: {$file->id}) does not exists");
        }

        $tmp = explode('::', $destination);

        if(count($tmp)==1){
            array_unshift($tmp, null);
        }

        list($new_disk, $path) = $tmp;

        $new_name       = $this->filesystem->basename($path);
        $new_path       = $this->filesystem->dirname($path);

        if($new_disk == $file->disk || $new_disk == null){
            Storage::disk($file->disk)->move($file->fullname, $destination);
        }
        else {
            Storage::disk($new_disk)->put(
                $destination,
                Storage::disk($file->disk)->get($file->fullname)
            );
        }
        $file->update([
            'name' => $new_name,
            'path' => $new_path,
        ]);
    }

    /** @inheritDoc */
    public function exists(File $file) : bool
    {
        return Storage::disk($file->disk)->exists($file->fullname);
    }

    /** @inheritDoc */
    public function renameFile(File $file, string $new_name)
    {
        $new_name       = $this->filesystem->basename($new_name);

        if(!$this->exists($file)){
            throw new \InvalidArgumentException("File {$file->fullname} (id: {$file->id}) does not exists");
        }

        $new_fullname = (!empty($file->path) ? $file->path . '/' : '') . $new_name;
        Storage::disk($file->disk)->move($file->fullname, $new_fullname);

        $file->update([
            'name' => $new_name,
        ]);
    }

    /** @inheritDoc */
    public function getFileContent(File $file) : string
    {
        if(!$this->exists($file)){
            throw new \InvalidArgumentException("File {$file->fullname} (id: {$file->id}) does not exists");
        }

        return Storage::disk($file->disk)->get($file->fullname);
    }

    public function getFileSize(File $file, string $measure_unit = 'bytes', string $conversion = 'binary') : float
    {
        if($conversion != 'binary' && $conversion != 'decimal'){
            throw new \InvalidArgumentException("Invalid conversion type '{$conversion}'");
        }

        $base = $conversion == 'binary' ? 1024 : 10;
        $exp  = 0;

        switch(strtolower($measure_unit)){
            case 'gigabytes':
            case 'gb':
                $exp = $conversion == 'binary' ? 3 : 9;
                break;

            case 'megabytes':
            case 'mb':
                $exp = $conversion == 'binary' ? 2 : 6;
                break;

            case 'kilobytes':
            case 'kb':
                $exp = $conversion == 'binary' ? 1 : 3;
                break;

            case 'bytes':
            case 'b':
                $exp = 0;
                break;

            default:
                throw new \InvalidArgumentException("Invalid measure unit '{$measure_unit}'");
        }

        return $file->size / ($base ** $exp);
    }
}
