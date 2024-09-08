<?php

namespace App\Controllers\Api;

use App\Interfaces\FileServiceInterface;
use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\ResponseInterface;
use App\Validators\FileValidator;

class FileController
{
    private FileServiceInterface $fileService;
    private DirectoryServiceInterface $directoryService;
    private ResponseInterface $response;

    public function __construct(
        FileServiceInterface $fileService,
        DirectoryServiceInterface $directoryService,
        ResponseInterface $response
    ) {
        $this->fileService = $fileService;
        $this->directoryService = $directoryService;
        $this->response = $response;
    }

    public function uploadFile(): void
    {
        $file = $_FILES['file'] ?? null;
        $parentId = $_POST['parentId'] ?? null;

        FileValidator::validateFile($file);

        if (!$file || !$parentId) {
            $this->response->error('Файл или ID родителя не предоставлены', 400);
            return;
        }

        $this->fileService->uploadFile($file, $parentId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        $this->response->success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function deleteFile(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $fileId = $data['id'] ?? null;

        if (!$fileId) {
            $this->response->error('ID файла не предоставлен', 400);
            return;
        }

        $this->fileService->deleteFile($fileId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        $this->response->success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function downloadFile(): void
    {
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            $this->response->error('ID файла не предоставлен', 400);
            return;
        }

        $file = $this->fileService->getFileById($fileId);

        if (!$file) {
            $this->response->error('Файл не найден', 404);
            return;
        }

        $filePath = $file['path'];
        $fileName = $file['name'];

        if (!file_exists($filePath)) {
            $this->response->error('Файл не существует', 404);
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    public function showImage(): void
    {
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            $this->response->error('ID файла не предоставлен', 400);
            return;
        }

        $file = $this->fileService->getFileById($fileId);

        if (!$file) {
            $this->response->error('Файл не найден', 404);
            return;
        }

        $filePath = __DIR__ . '/../../../storage/uploads/' . $file['path'];
        $relativeFilePath = str_replace('/var/www/storage/uploads/', '', $filePath);

        if (!file_exists($relativeFilePath)) {
            $this->response->error('Файл не существует', 404);
            return;
        }

        $mimeType = mime_content_type($relativeFilePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($relativeFilePath));

        readfile($relativeFilePath);
        exit;
    }
}
