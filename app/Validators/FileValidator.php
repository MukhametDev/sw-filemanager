<?php

namespace App\Validators;

class FileValidator
{
    private const ALLOWED_FILE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private const MAX_FILE_SIZE_BYTES = 20 * 1024 * 1024;

    public static function isEmpty(array|int $file): void
    {
        if (empty($file)) {
            throw new \Exception("Переменная пустая");
        }
    }

    public static function validateTypeOfFile(array $file): void
    {
        if (!in_array($file['type'], self::ALLOWED_FILE_TYPES)) {
            throw new \Exception("Недопустимый тип файла");
        }
    }

    public static function validateSizeOfFile(array $file): void
    {
        if ($file['size'] > self::MAX_FILE_SIZE_BYTES) {
            throw new \Exception("Размер файла превышает " . (self::MAX_FILE_SIZE_BYTES / 1024 / 1024) . "MB");
        }
    }

    public static function checkFolderExists(string $baseUploadDir): void
    {
        if (!$baseUploadDir) {
            throw new \Exception("Базовая директория для загрузки не найдена");
        }
    }
}