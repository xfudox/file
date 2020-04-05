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
            'size'      => $uploaded_file->getSize(),
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

        $pattern = '/((?<disk>[\w-]+)::)?(?<fullname>[\w\/.\-\()]+)$/';
        $matches = null;
        preg_match($pattern, 'fullname', $matches, PREG_UNMATCHED_AS_NULL);

        $source_disk    = $file->disk;
        $source         = $file->fullname;
        
        $destination_disk   = $matches['disk'];
        $new_fullname       = $matches['fullname'];

        $new_name   = $this->filesystem->basename($new_fullname);
        $new_path   = $this->filesystem->dirname($new_fullname);

        if($destination_disk == $source_disk || $destination_disk == null){
            Storage::disk($source_disk)->move($source, $destination);
        }
        else {
            Storage::disk($destination_disk)->put(
                $destination,
                Storage::disk($source_disk)->get($source)
            );
            Storage::disk($source_disk)->delete($source);
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
    public function getFileContent(File $file) : string
    {
        if(!$this->exists($file)){
            throw new \InvalidArgumentException("File {$old_fullname} (id: {$file->id}) does not exists");
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
