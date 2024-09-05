<?php

namespace App\Controllers;

class Controller
{
    public function includeCSS(string $file): string
    {
        return '/css/' . $file;
    }

    public function includeJS(string $file): string
    {
        return '/js/' . $file;
    }
}
