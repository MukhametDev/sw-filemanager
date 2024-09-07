<?php

namespace App\Models;

class Directory
{
    public int $id;
    public ?int $parent_id;
    public string $name;

    public function __construct($id, $name, $parentId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent_id = $parentId;
    }
}
