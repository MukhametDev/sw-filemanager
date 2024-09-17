<?php

namespace App\View;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        $tree = self::renderTree($directoriesTree);
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

    public static function renderTree($directories)
    {
        if (empty($directories)) {
            return '';
        }

        $html = '<ul class="sidebar__directories">';

        foreach ($directories as $directory) {
            $html .= '<li class="sidebar__directory" data-id="' . htmlspecialchars($directory['id']) . '">'
                . htmlspecialchars($directory['name']);

            if (!empty($directory['children'])) {
                $html .= self::renderTree($directory['children']);
            }

            if (!empty($directory['files'])) {
                $html .= '<ul class="sidebar__files">';
                foreach ($directory['files'] as $file) {
                    $html .= '<li class="sidebar__file" data-id="' . htmlspecialchars($file['id']) . '">'
                        . htmlspecialchars($file['name']) . '</li>';
                }
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
