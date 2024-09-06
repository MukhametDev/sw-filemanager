<?php

namespace App\Services;

use App\Repository\DirectoryRepository;
use App\Repository\FileRepository;

class DirectoryService
{
    protected $directoryRepository;
    protected $fileRepository;

    public function __construct()
    {
        $this->directoryRepository = new DirectoryRepository();
        $this->fileRepository = new FileRepository();
    }

    public function createDirectory(string $name, ?int $parentId): int
    {
        if (empty($name)) {
            throw new \Exception("Имя директории не может быть пустым");
        }

        return $this->directoryRepository->createDirectory($name, $parentId);
    }

    public function deleteDirectoryWithContents(int $directoryId): void
    {
        $this->directoryRepository->deleteDirectoryWithContents($directoryId);
    }

    public function getAllDirectoriesAndFiles(): array
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();

        return ['directories' => $directories, 'files' => $files];
    }
}
