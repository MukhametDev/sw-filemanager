<?php

namespace App\Controllers;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $file = __DIR__ . '/../View/' . $view . '.php';

        if (!file_exists($file)) {
            abort("View {$view} not found", 404);
        }

        extract($data);
        include $file;
    }

    public function includeCSS(string $file): string
    {
        return '/css/' . $file;
    }

    public function includeJS(string $file): string
    {
        return '/js/' . $file;
    }
}
