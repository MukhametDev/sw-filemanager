<?php

namespace App\Interfaces;

interface RequestHandlerInterface
{
    public function getJsonData(): array;
}