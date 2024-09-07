<?php

namespace App\Models;

class FileModel
{
    public $id;
    public $name;
    public $directory_id;
    public $size;
    public $mime_type;

    public function __construct($id, $filename, $directory_id, $size, $mime_type)
    {
        $this->id = $id;
        $this->name = $filename;
        $this->directory_id = $directory_id;
        $this->size = $size;
        $this->mime_type = $mime_type;
    }
}
