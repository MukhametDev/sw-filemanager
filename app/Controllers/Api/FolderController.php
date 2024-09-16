<?php

namespace App\Controllers\Api;

use App\Handlers\RequestHandler;
use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\ResponseInterface;
use App\Validators\DirectoryValidator;

class FolderController
{
    public function __construct(
        private DirectoryServiceInterface $directoryService,
        private ResponseInterface $response,
        private RequestHandler $requestHandler,
        private DirectoryValidator $directoryValidator
    ) {}

    public function add(): void
    {
        $input = $this->requestHandler->getJsonData();
        $name = $input['folderName'] ?? '';
        $parentId = $input['parentId'] ?? null;

        if ($this->directoryValidator->isEmpty($name)) {
            $this->response->error('Имя директории обязательно', 400);
            return;
        }

        if ($this->directoryValidator->validateLengthOfName($name)) {
            $this->response->error('Имя директории должно быть не более 50 символов', 400);
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
        $data = $this->requestHandler->getJsonData();
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
