<?php

namespace App\Interfaces;

interface BuildTreeInterface
{
    public function buildTreeHtml($directories, $files, $parentId = null);
}