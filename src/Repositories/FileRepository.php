<?php

namespace xfudox\File\Repositories;

use Illuminate\Http\UploadedFile;
use xfudox\File\Models\File;

interface FileRepository
{
    /**
     * Create new file from uploaded file.
     *
     * @param UploadedFile $file
     * @param string $full_path
     * @return File
     */
    public function createFromUploadedFile(UploadedFile $file, string $full_path = 'public::/') : File;

    /**
     * Return file url.
     *
     * @param File $file
     * @return string
     */
    public function getFileUrl(File $file) : string;

    /**
     * Move file between disks and paths
     *
     * @param File $file
     * @param string $destination
     * @return void
     */
    public function moveFile(File $file, string $destination);

    /**
     * Check if file exists on disk.
     *
     * @param File $file
     * @return boolean
     */
    public function exists(File $file) : bool;

    /**
     * Retrieve file content.
     *
     * @param File $file
     * @return string
     */
    public function getFileContent(File $file) : string;
}
