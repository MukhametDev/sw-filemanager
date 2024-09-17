<?php

namespace App\Interfaces;

use App\Models\FileModel;

interface FileRepositoryInterface
{
    public function getFileById(int $id): ?array;
    public function getAllFiles(): array;
    public function saveFile(string $name, int $directoryId, int $size, string $mimeType, string $path): void;
    public function deleteFile(int $fileId): void;
    public function getFilesByDirectoryId(int $directoryId): array;
}
