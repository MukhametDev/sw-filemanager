<?php

namespace App\Controllers;

use App\DB\Database;
use App\Repository\DirectoryRepository;
use App\Repository\FileRepository;
use App\Services\BuildTreeService;
use App\View\View;

class HomeController
{
    public function __construct(
        private BuildTreeService $buildTreeService,
        private FileRepository $fileRepository,
        private DirectoryRepository $directoryRepository
    ) {}
    public function index()
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();
        $treeHtml = $this->buildTreeService->buildTreeHtml($directories, $files);

        $data = [
            'title' => 'Файловый менеджер',
            'directories' => $treeHtml
        ];

        View::render('home', $data);
    }
}
