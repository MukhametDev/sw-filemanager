<?php

namespace App\Validators;

class FileValidator
{

    public static function isEmpty(array $file): void
    {
        if (empty($file)) {
            throw new \Exception("Файл не может быть пустым");
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
}
