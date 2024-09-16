<?php

namespace App\Interfaces;

interface  DirectoryValidatorInterface
{
    public static function validateName(string $name): void;
}
