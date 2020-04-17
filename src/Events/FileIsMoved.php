<?php

namespace xfudox\File\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use xfudox\File\Models\File;

class FileIsMoved extends Event
{
    use SerializesModels;

    public $file;

    /**
     * Create a new event instance.
     *
     * @param  File  $file
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }
}