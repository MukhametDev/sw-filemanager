<?php

namespace App\Interfaces;

use App\Models\Directory;

interface DirectoryRepositoryInterface
{
    public function getAllDirectories(): array;
    public function getSubdirectories(int $parentId): array;
    public function createDirectory(string $name, ?int $parentId): int;
    public function getDirectoryById(int $id): ?array;
    public function deleteDirectory(int $directoryId): void;
}
