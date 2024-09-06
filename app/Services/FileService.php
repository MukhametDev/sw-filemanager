<?php

namespace App\Services;

use App\Repository\FileRepository;

class FileService
{
    protected $fileRepository;

    public function __construct()
    {
        $this->fileRepository = new FileRepository();
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

        $uploadDir = __DIR__ . '/../../storage/uploads';
        $filePath = $uploadDir . '/' . basename($file['name']);

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception("Ошибка загрузки файла");
        }

        $this->fileRepository->saveFile($file['name'], $parentId, $file['size'], $file['type'], $filePath);
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
