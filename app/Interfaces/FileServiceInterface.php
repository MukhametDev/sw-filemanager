<?php

namespace App\Interfaces;

interface FileServiceInterface
{
    public function uploadFile(array $file, int $parentId): void;
    public function deleteFile(int $fileId): void;
    public function getFileById(int $fileId): ?array;
}
