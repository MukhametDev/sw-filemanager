<?php

namespace App\Services;

use App\Interfaces\DirectoryRepositoryInterface;
use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\FileRepositoryInterface;
use App\Interfaces\ResponseInterface;
use App\Utils\FileManager;
use Exception;

class DirectoryService implements DirectoryServiceInterface
{
    public function __construct(
        private DirectoryRepositoryInterface $directoryRepository,
        private FileRepositoryInterface $fileRepository,
        private FileManager $fileManager,
        private ResponseInterface $response
    ) {}

    public function createDirectory(string $name, ?int $parentId): int
    {
        $directoryId = $this->directoryRepository->createDirectory($name, $parentId);

        if ($parentId === null) {
            $parentPath = '';
        } else {
            $parentPath = $this->getDirectoryPathById($parentId);
        }

        $fullPath = __DIR__ . '/../../storage/uploads/' . $parentPath . '/' . $name;
        $this->fileManager->createDirectoryIfNotExists($fullPath);

        return $directoryId;
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
        $this->fileManager->deleteDirectory($fullPath);

        $this->directoryRepository->deleteDirectory($directoryId);
    }

    public function getAllDirectoriesAndFiles(): array
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();

        return [
            'directories' => $directories,
            'files' => $files
        ];
    }

    public function getDirectoryPathById(int $directoryId): string
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
