<?php

namespace App\Validators;

class FileValidator
{
    public static function validateFile(array $file): void
    {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception("Недопустимый тип файла");
        }

        if ($file['size'] > 20000000) {
            throw new \Exception("Размер файла превышает 20MB");
        }
    }
}
