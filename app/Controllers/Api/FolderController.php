<?php

namespace App\Controllers\Api;

use App\Services\DirectoryService;
use App\Validators\DirectoryValidator;

class FolderController
{
    private $directoryService;

    public function __construct()
    {
        $this->directoryService = new DirectoryService();
    }

    public function add()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['folderName'] ?? '';
        $parentId = $input['parentId'] ?? null;

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Folder name is required']);
            return;
        }

        try {
            // Валидация имени директории
            DirectoryValidator::validateName($name);

            // Создание директории в базе данных
            $newDirectoryId = $this->directoryService->createDirectory($name, $parentId);

            // Получение пути родительской директории
            $parentPath = $this->directoryService->getDirectoryPathById($parentId);
            $fullPath = __DIR__ . '/../../../storage/uploads/' . $parentPath . '/' . $name;

            // Создание директории на сервере
            if (!is_dir($fullPath)) {
                if (!mkdir($fullPath, 0777, true)) {
                    throw new \Exception("Не удалось создать директорию: " . $fullPath);
                }
            }

            // Возвращаем обновлённое дерево файлов
            $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
            echo json_encode([
                'success' => true,
                'directories' => $updatedData['directories'],
                'files' => $updatedData['files']
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteFolder()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $folderId = $data['id'] ?? null;

        if (!$folderId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Folder ID not provided']);
            return;
        }

        try {
            $this->directoryService->deleteDirectoryWithContents($folderId);

            $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
            echo json_encode([
                'success' => true,
                'directories' => $updatedData['directories'],
                'files' => $updatedData['files']
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getDirectories()
    {
        try {
            $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
            echo json_encode([
                'success' => true,
                'directories' => $updatedData['directories'],
                'files' => $updatedData['files']
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
