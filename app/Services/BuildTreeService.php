<?php

namespace App\Services;

class BuildTreeService
{
    public function buildTreeHtml($directories, $files, $parentId = null)
    {
        $html = '<ul class="sidebar__directories">';

        foreach ($directories as $directory) {
            if ($directory->parent_id == $parentId) {
                $html .= '<li class="sidebar__directory" data-id="' . $directory->id . '">'
                    . htmlspecialchars($directory->name);

                $html .= $this->buildTreeHtml($directories, $files, $directory->id);

                $html .= '<ul class="sidebar__directories">';
                foreach ($files as $file) {
                    if ($file->directory_id == $directory->id) {
                        $html .= '<li class="sidebar__file" data-id="' . $file->id . '"> '
                            . htmlspecialchars($file->name) .
                            '</li>';
                    }
                }
                $html .= '</ul>';

                $html .= '</li>';
            }
        }

        $html .= '</ul>';

        return $html;
    }
}