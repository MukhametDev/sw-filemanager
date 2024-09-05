<?php

namespace App\View;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        require __DIR__ . "/templates/{$view}.php";
    }

    public static function renderError($message)
    {
        self::render('error', ['message' => $message]);
    }

    public static function includeCSS(string $file): string
    {
        return '/css/' . $file;
    }

    public static function includeJS(string $file): string
    {
        return '/js/' . $file;
    }
}
