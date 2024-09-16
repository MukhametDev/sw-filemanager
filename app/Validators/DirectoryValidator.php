<?php

namespace App\Validators;

class DirectoryValidator
{
    public static function isEmpty(string $name): void
    {
        if (empty($name)) {
            throw new \Exception("Имя директории не может быть пустым");
        }
    }

    public static function validateLengthOfName(string $name): void
    {
        if (strlen($name) > 50) {
            throw new \Exception("Имя директории слишком длинное");
        }
    }
}
