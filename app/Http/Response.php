<?php

namespace App\Http;

use App\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    protected array $headers = [];
    protected $body;
    protected int $statusCode = 200;

    public function setHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setBody($body): void
    {
        if (is_array($body) || is_object($body)) {
            $this->setHeader('Content-Type: application/json');
            $this->body = json_encode($body);
        } else {
            $this->body = $body;
        }
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $header) {
            header($header);
        }

        echo $this->body;
        exit;
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        $response = new self();
        $response->setStatusCode($statusCode);
        $response->setBody($data);
        $response->send();
    }

    public static function success(array $data = [], int $statusCode = 200): void
    {
        self::json($data, $statusCode);
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::json(['error' => $message], $statusCode);
    }
}
