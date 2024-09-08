<?php

namespace App\Controllers\Api;

use App\Services\DirectoryService;
use App\Validators\DirectoryValidator;
use App\Http\Response;

class FolderController
{
    private DirectoryService $directoryService;

    public function __construct(DirectoryService $directoryService)
    {
        $this->directoryService = $directoryService;
    }

    public function add(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['folderName'] ?? '';
        $parentId = $input['parentId'] ?? null;

        if (empty($name)) {
            Response::error('Имя директории обязательно', 400);
        }

        DirectoryValidator::validateName($name);
        $this->directoryService->createDirectory($name, $parentId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        Response::success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function deleteFolder(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $folderId = $data['id'] ?? null;

        if (!$folderId) {
            Response::error('ID директории не предоставлен', 400);
        }

        $this->directoryService->deleteDirectoryWithContents($folderId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        Response::success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function getDirectories(): void
    {
        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        Response::success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }
}
