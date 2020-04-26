<?php

namespace xfudox\File\Repositories;

use Illuminate\Http\UploadedFile;
use xfudox\File\Models\File;

interface FileRepository
{
    /**
     * Split sting to get data about destination file.
     *
     * @param string $destination
     * @return array
     */
    public function getDataFromDestination(string $destination) : array;

    /**
     * Create new file from uploaded file.
     *
     * @param UploadedFile $file
     * @param string $destination
     * @return File
     */
    public function createFromUploadedFile(UploadedFile $uploaded_file, string $destination = '', ?string $disk = null) : File;

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
    
    /**
     * Return file size in different formats.
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
     * @param File $file
     * @param string $measure_unit
     * @param string $conversion
     * @return float
     */
    public function getFileSize(File $file, string $measure_unit = 'bytes', string $conversion = 'binary') : float;
}
