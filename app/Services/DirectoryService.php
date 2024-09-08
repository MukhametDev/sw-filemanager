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
        // Получаем все файлы в директории
        $files = $this->fileRepository->getFilesByDirectoryId($directoryId);
        foreach ($files as $file) {
            // Удаляем каждый файл с диска и из базы данных
            if (file_exists($file['path'])) {
                unlink($file['path']);
            }
            $this->fileRepository->deleteFile($file['id']);
        }

        // Получаем все поддиректории
        $subdirectories = $this->directoryRepository->getSubdirectories($directoryId);

        foreach ($subdirectories as $subdirectory) {
            // Рекурсивно удаляем каждую поддиректорию
            $this->deleteDirectoryWithContents($subdirectory['id']);
        }

        // Удаляем саму директорию с диска
        $directoryPath = $this->getDirectoryPathById($directoryId);
        $fullPath = realpath(__DIR__ . '/../../storage/uploads/' . $directoryPath);
        if (is_dir($fullPath)) {
            rmdir($fullPath);
        }

        // Удаляем директорию из базы данных
        $this->directoryRepository->deleteDirectory($directoryId);
    }


    public function getAllDirectoriesAndFiles(): array
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();

        return ['directories' => $directories, 'files' => $files];
    }

    public function getDirectoryPathById($directoryId)
    {
        $path = '';
        while ($directoryId) {
            $directory = $this->directoryRepository->getDirectoryById($directoryId);
            $path = $directory['name'] . '/' . $path;
            $directoryId = $directory['parent_id'];
        }
        return rtrim($path, '/'); // удаляем последний "/"
    }

}
