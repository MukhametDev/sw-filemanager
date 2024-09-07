<?php

namespace App\Validators;

class DirectoryValidator
{
    public static function validateName(string $name): void
    {
        if (empty($name)) {
            throw new \Exception("Имя директории не может быть пустым");
        }

        if (strlen($name) > 50) {
            throw new \Exception("Имя директории слишком длинное");
        }
    }
}
