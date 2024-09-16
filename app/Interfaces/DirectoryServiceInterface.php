<?php

namespace App\Interfaces;

interface DirectoryServiceInterface
{
    public function createDirectory(string $name, ?int $parentId): int;
    public function deleteDirectoryWithContents(int $directoryId): void;
    public function getAllDirectoriesAndFiles(): array;
    public function getDirectoryPathById(int $directoryId): string;
}
