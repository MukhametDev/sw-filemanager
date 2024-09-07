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
        $name = $input['folderName'];
        $parentId = $input['parentId'] ?? null;

        try {
            DirectoryValidator::validateName($name);
            $this->directoryService->createDirectory($name, $parentId);

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
        $folderId = $data['id'];

        try {
            $this->directoryService->deleteDirectoryWithContents($folderId);

            $updatedData = $this->directoryService->getAllDirectoriesAndFiles();

            echo json_encode([
                'success' => true,
                'directories' => $updatedData['directories'],
                'files' => $updatedData['files']
            ]);
        } catch (\Exception $e) {
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
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
