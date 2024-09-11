<?php

namespace App\Handlers;

class RequestHandler
{
    public function getJsonData(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        return $data;
    }
}
