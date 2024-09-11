<?php

namespace App\Utils;

class FileManager
{
    public function uploadFile(array $file, string $fullUploadPath): string
    {
        $this->createDirectoryIfNotExists($fullUploadPath);

        $uniqueFileName = uniqid() . '_' . basename($file['name']);
        $filePath = $fullUploadPath . '/' . $uniqueFileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception("Ошибка при загрузке файла");
        }

        return $filePath;
    }

    public function deleteFile(array $file): void
    {
        if (file_exists($file['path'])) {
            unlink($file['path']);
        }
    }

    public function checkFileExists(array $file): void
    {
        if (!$file) {
            throw new \Exception("Файл не найден");
        }
    }

    public function createDirectoryIfNotExists(string $fullUploadPath): void
    {
        if(!is_dir($fullUploadPath)) {
            if(!mkdir($fullUploadPath, 0777, true)) {
                throw new \Exception("Не удалось создать директорию: " . $fullUploadPath);
            }
        }
    }

    public function deleteDirectory(string $filePath): void
    {
        if(is_dir($filePath)) {
            rmdir($filePath);
        }
    }
}