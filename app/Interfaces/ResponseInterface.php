<?php

namespace App\Interfaces;

interface ResponseInterface
{
    public function setHeader(string $header): void;
    public function setStatusCode(int $statusCode): void;
    public function setBody($body): void;
    public function send(): void;
}
