<?php

namespace App\Services;

use App\Interfaces\DirectoryRepositoryInterface;
use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\FileRepositoryInterface;
use Exception;

class DirectoryService implements DirectoryServiceInterface
{
    private DirectoryRepositoryInterface $directoryRepository;
    private FileRepositoryInterface $fileRepository;

    public function __construct(
        DirectoryRepositoryInterface $directoryRepository,
        FileRepositoryInterface      $fileRepository
    )
    {
        $this->directoryRepository = $directoryRepository;
        $this->fileRepository = $fileRepository;
    }

    public function createDirectory(string $name, ?int $parentId): int
    {
        if (empty($name)) {
            throw new \Exception("Имя директории не может быть пустым");
        }

        $directoryId = $this->directoryRepository->createDirectory($name, $parentId);

        if ($parentId === null) {
            $parentPath = '';
        } else {
            $parentPath = $this->getDirectoryPathById($parentId);
        }

        $fullPath = __DIR__ . '/../../storage/uploads/' . $parentPath . '/' . $name;

        if (!is_dir($fullPath)) {
            if (!mkdir($fullPath, 0777, true)) {
                throw new \Exception("Не удалось создать директорию: " . $fullPath);
            }
        }

        return $directoryId;
    }


    public function deleteDirectoryWithContents(int $directoryId): void
    {
// Удаление всех файлов из директории
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
