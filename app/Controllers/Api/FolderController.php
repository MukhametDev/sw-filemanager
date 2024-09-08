<?php

namespace App\Controllers\Api;

use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\ResponseInterface;
use App\Validators\DirectoryValidator;

class FolderController
{
    private DirectoryServiceInterface $directoryService;
    private ResponseInterface $response;

    public function __construct(
        DirectoryServiceInterface $directoryService,
        ResponseInterface $response
    ) {
        $this->directoryService = $directoryService;
        $this->response = $response;
    }

    public function add(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['folderName'] ?? '';
        $parentId = $input['parentId'] ?? null;

        DirectoryValidator::validateName($name);

        if (empty($name)) {
            $this->response->error('Имя директории обязательно', 400);
            return;
        }


        $this->directoryService->createDirectory($name, $parentId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        $this->response->success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function deleteFolder(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $folderId = $data['id'] ?? null;

        if (!$folderId) {
            $this->response->error('ID директории не предоставлен', 400);
            return;
        }

        $this->directoryService->deleteDirectoryWithContents($folderId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        $this->response->success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function getDirectories(): void
    {
        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        $this->response->success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }
}
