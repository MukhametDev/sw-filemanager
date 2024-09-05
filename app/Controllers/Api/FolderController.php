<?php

namespace App\Controllers\Api;

use App\DB\Database;
use App\Repository\DirectoryRepository;
use App\Repository\FileRepository;

class FolderController
{
    private $directoryRepository;
    private $fileRepository;

    public function __construct(Database $db)
    {
        $this->fileRepository = new FileRepository($db);
        $this->directoryRepository = new DirectoryRepository($db);
    }

    public function add()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['folderName'] ?? '';
        $parentId = $input['parentId'] ?? null;

        if ($name) {
            $directoryId = $this->directoryRepository->createDirectory($name, $parentId);

            // Получаем все директории и файлы после добавления новой директории
            $directories = $this->directoryRepository->getAllDirectories();
            $files = $this->fileRepository->getAllFiles();

            echo json_encode([
                'success' => true,
                'directories' => $directories,
                'files' => $files
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Имя директории не может быть пустым'
            ]);
        }
    }

    public function  getDirectories()
    {
        $directories = $this->directoryRepository->getAllDirectories();
        $files = $this->fileRepository->getAllFiles();
        echo json_encode([
            'success' => true,
            'directories' => $directories,
            'files' => $files
        ]);
    }

    public function deleteFolder()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $folderId = $data['id'];

        // Получаем информацию о директории из базы данных
        $directory = $this->directoryRepository->getDirectoryById($folderId);

        if (!$directory) {
            echo json_encode(['error' => 'Directory not found']);
            return;
        }

        // Рекурсивно удаляем все файлы и поддиректории
        $this->directoryRepository->deleteDirectoryWithContents($folderId);

        echo json_encode(['success' => true]);
    }
}