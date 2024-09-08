<?php

namespace App\Services;

use App\Repository\FileRepository;

class FileService
{
    private FileRepository $fileRepository;
    private DirectoryService $directoryService;

    public function __construct(FileRepository $fileRepository, DirectoryService $directoryService)
    {
        $this->fileRepository = $fileRepository;
        $this->directoryService = $directoryService;
    }

    public function uploadFile(array $file, int $parentId): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception("Недопустимый тип файла");
        }

        if ($file['size'] > 20000000) {
            throw new \Exception("Размер файла превышает 20MB");
        }

        $baseUploadDir = realpath(__DIR__ . '/../../storage/uploads');
        if (!$baseUploadDir) {
            throw new \Exception("Базовая директория для загрузки не найдена");
        }

        $parentPath = $this->directoryService->getDirectoryPathById($parentId);
        $fullUploadPath = $baseUploadDir . ($parentPath ? '/' . $parentPath : '');

        if (!is_dir($fullUploadPath)) {
            if (!mkdir($fullUploadPath, 0777, true)) {
                throw new \Exception("Ошибка при создании директории для загрузки: " . $fullUploadPath);
            }
        }

        $uniqueFileName = uniqid() . '_' . basename($file['name']);
        $filePath = $fullUploadPath . '/' . $uniqueFileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception("Ошибка при загрузке файла");
        }

        $this->fileRepository->saveFile($uniqueFileName, $parentId, $file['size'], $file['type'], $filePath);
    }

    public function deleteFile(int $fileId): void
    {
        $file = $this->fileRepository->getFileById($fileId);
        if (!$file) {
            throw new \Exception("Файл не найден");
        }

        if (file_exists($file['path'])) {
            unlink($file['path']);
        }

        $this->fileRepository->deleteFile($fileId);
    }

    public function getFileById(int $fileId): ?array
    {
        return $this->fileRepository->getFileById($fileId);
    }
}
