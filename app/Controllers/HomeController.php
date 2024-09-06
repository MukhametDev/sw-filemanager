<?php

namespace App\Controllers;

use App\DB\Database;
use App\Repository\DirectoryRepository;
use App\Repository\FileRepository;
use App\View\View;

class HomeController
{
    private $fileRepository;
    private $directoryRepository;
    public function __construct()
    {
        $this->directoryRepository = new DirectoryRepository();
        $this->fileRepository = new FileRepository();
    }
    public function index()
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();
        $treeHtml = $this->buildTreeHtml($directories, $files);

        $data = [
            'title' => 'Файловый менеджер',
            'directories' => $treeHtml
        ];

        View::render('home', $data);
    }

    protected function buildTreeHtml($directories, $files, $parentId = null)
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
