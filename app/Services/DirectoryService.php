<?php

namespace App\Services;

use App\Repository\DirectoryRepository;
use App\Repository\FileRepository;

class DirectoryService
{
    private DirectoryRepository $directoryRepository;
    private FileRepository $fileRepository;

    public function __construct(DirectoryRepository $directoryRepository, FileRepository $fileRepository)
    {
        $this->directoryRepository = $directoryRepository;
        $this->fileRepository = $fileRepository;
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
        $files = $this->fileRepository->getFilesByDirectoryId($directoryId);
        foreach ($files as $file) {
            if (file_exists($file['path'])) {
                unlink($file['path']);
            }
            $this->fileRepository->deleteFile($file['id']);
        }

        $subdirectories = $this->directoryRepository->getSubdirectories($directoryId);
        foreach ($subdirectories as $subdirectory) {
            $this->deleteDirectoryWithContents($subdirectory['id']);
        }

        $directoryPath = $this->getDirectoryPathById($directoryId);
        $fullPath = realpath(__DIR__ . '/../../storage/uploads/' . $directoryPath);
        if (is_dir($fullPath)) {
            rmdir($fullPath);
        }

        $this->directoryRepository->deleteDirectory($directoryId);
    }

    public function getAllDirectoriesAndFiles(): array
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();

        return ['directories' => $directories, 'files' => $files];
    }

    public function getDirectoryPathById($directoryId): string
    {
        $path = '';
        while ($directoryId) {
            $directory = $this->directoryRepository->getDirectoryById($directoryId);
            $path = $directory['name'] . '/' . $path;
            $directoryId = $directory['parent_id'];
        }
        return rtrim($path, '/');
    }
}
