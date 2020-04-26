<?php

namespace xfudox\File\Repositories\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use xfudox\File\Events\FileIsCreated;
use xfudox\File\Events\FileIsMoved;
use xfudox\File\Events\FileIsRenamed;
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
    public function createFromUploadedFile(UploadedFile $uploaded_file, string $destination = '/', ?string $disk = null) : File
    {
        if(null == $disk || '' == $disk){
            $disk = config('filesystems.default');
        }

        $dest_data = $this->getDataFromDestination($destination);
        $name   = $dest_data['name'] ?? $uploaded_file->getClientOriginalName();
        $path   = $dest_data['path'];
        $ext    = $dest_data['extension'] ?? $uploaded_file->getClientOriginalExtension();

        $file = File::create([
            'name'      => $name,
            'extension' => $ext,
            // 'mime'      => $uploaded_file->getClientMimeType(),
            'size'      => $uploaded_file->getSize(),
            'disk'      => $disk,
            'path'      => $path,
        ]);

        $uploaded_file->storeAs($destination, $uploaded_file->getClientOriginalName(), ['disk' => $disk]);

        event(new FileIsCreated($file));

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

        $old_name           = $file->name;
        $source_disk        = $file->disk;
        $source_fullname    = $file->fullname;

        $pattern = '/((?<disk>[\w-]+)::)?(?<fullname>[\w\/.\-\()]+)$/';
        $matches = null;
        preg_match($pattern, $destination, $matches, PREG_UNMATCHED_AS_NULL);
        
        $destination_disk       = $matches['disk'];
        $destination_fullname   = $matches['fullname'];

        $new_name   = $this->filesystem->basename($destination_fullname);
        $new_path   = $this->filesystem->dirname($destination_fullname);

        if($destination_disk == $source_disk || $destination_disk == null){
            Storage::disk($source_disk)->move($source_fullname, $destination_fullname);
        }
        else {
            Storage::disk($destination_disk)->put(
                $destination_fullname,
                Storage::disk($source_disk)->get($source_fullname)
            );
            Storage::disk($source_disk)->delete($source_fullname);
            $file->disk = $destination_disk;
        }
        $file->update([
            'name' => $new_name,
            'path' => $new_path,
        ]);

        event(new FileIsMoved($file));
        if($new_name != $old_name){
            event(new FileIsRenamed($file));
        }

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

    /** @inheritDoc */
    public function getDataFromDestination(string $destination) : array
    {
        if($destination == '' || $destination == '/'){
            return [
                'path'      => '/',
                'name'      => null,
                'extension' => null,
            ];
        }

        if($destination[0] == '/'){
            $destination = substr($destination, 1);
        }

        $x = strrpos($destination, '/');
        // e.g.: 'file.png'
        if(false === $x){
            $path = '/';
            $name = $destination;
        }
        // e.g.: 'path/to/'
        elseif(strlen($destination)-1 ==$x){
            return [
                'path'      => $destination,
                'name'      => null,
                'extension' => null,
            ];
        }
        else{
            $path = substr($destination, 0, $x+1);
            $name = substr($destination, $x+1);
        }

        $y = strrpos($name, '.');
        // e.g.: 'path/to/file'
        if(false === $y){
            $extension = null;
        }
        // e.g.: 'path/to/.hidden_file'
        elseif(0 === $y){
            return [
                'path'      => $path,
                'name'      => $name,
                'extension' => null,
            ];
        }
        else{
            $extension = substr($name, $y+1);
        }

        return [
            'path'      => $path,
            'name'      => $name,
            'extension' => $extension,
        ];
    }
}
