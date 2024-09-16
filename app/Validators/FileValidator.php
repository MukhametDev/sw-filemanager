<?php

namespace App\Validators;

class FileValidator
{

    public static function isEmpty(array|int $file): void
    {
        if (empty($file)) {
            throw new \Exception("Переменная пустая");
        }
    }

    public static function validateTypeOfFile(array $file): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception("Недопустимый тип файла");
        }
    }

    public static function validateSizeOfFile(array $file): void
    {
        if ($file['size'] > 20000000) {
            throw new \Exception("Размер файла превышает 20MB");
        }
    }

    public static function checkFolderExists(string $baseUploadDir): void
    {
        if (!$baseUploadDir) {
            throw new \Exception("Базовая директория для загрузки не найдена");
        }
    }
}
