<?php

namespace App\Controllers\Api;

use App\Handlers\RequestHandler;
use App\Interfaces\FileServiceInterface;
use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\ResponseInterface;
use App\Validators\FileValidator;

class FileController
{
    private FileServiceInterface $fileService;
    private DirectoryServiceInterface $directoryService;
    private ResponseInterface $response;
    private FileValidator $fileValidator;
    private RequestHandler $requestHandler;

    public function __construct(
        FileServiceInterface $fileService,
        DirectoryServiceInterface $directoryService,
        ResponseInterface $response,
        FileValidator $fileValidator,
        RequestHandler $requestHandler
    ) {
        $this->fileService = $fileService;
        $this->directoryService = $directoryService;
        $this->response = $response;
        $this->fileValidator = $fileValidator;
        $this->requestHandler = $requestHandler;
    }

    public function uploadFile(): void
    {
        $file = $_FILES['file'] ?? null;
        $parentId = $_POST['parentId'] ?? null;

        if($this->isInvalidFileRequest($file, $parentId)) {
            return;
        }

        if(!$this->validateFile($file, $parentId)) {
            return;
        }

        $this->fileService->uploadFile($file, $parentId);
        $this->respondWithUpdateData();
    }

    public function deleteFile(): void
    {
        $fileId = $this->requestHandler->getJsonData()['id'] ?? null;

        if (!$fileId) {
            $this->response->error('ID файла не предоставлен', 400);
            return;
        }

        $this->fileService->deleteFile($fileId);
        $this->respondWithUpdateData();
    }

    public function downloadFile(): void
    {
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            $this->response->error('ID файла не предоставлен', 400);
            return;
        }

        $file = $this->fileService->getFileById($fileId);

        if (!$file || !file_exists($file['path'])) {
            $this->response->error('Файл не найден', 404);
            return;
        }

        $this->outputFile($file['name'], $file['path']);
    }

    public function showImage(): void
    {
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            $this->response->error('ID файла не предоставлен', 400);
            return;
        }

        $file = $this->fileService->getFileById($fileId);

        $filePath = __DIR__ . '/../../../storage/uploads/' . $file['path'];
        $relativeFilePath = str_replace('/var/www/storage/uploads/', '', $filePath);

        if ($this->fileValidator->isEmpty($file) || !file_exists($relativeFilePath)) {
            $this->response->error('Файл не найден', 404);
            return;
        }

        $this->outputImage($relativeFilePath);
    }

    private function isInvalidFileRequest($file, $parentId): bool
    {
        if ($this->fileValidator->isEmpty($file) || !$parentId) {
            $this->response->error('Файл или ID родителя не предоставлены', 400);
            return true;
        }
        return false;
    }

    private function respondWithUpdateData(): void
    {
        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();

        $this->response->success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    private function validateFile(array $file, int $parentId): bool
    {
        if ($this->fileValidator->isEmpty($file) || !$parentId) {
            $this->response->error('Файл или ID родителя не предоставлены', 400);
            return false;
        }

        if($this->fileValidator->validateTypeOfFile($file)) {
            $this->response->error('Недопустимый тип файла', 400);
            return false;
        }

        if($this->fileValidator->validateSizeOfFile($file)) {
            $this->response->error('Размер файла превышает 20MB', 400);
            return false;
        }

        return true;
    }

    private function outputFile(string $filename, string $filepath): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        readfile($filepath);
        exit;
    }

    private function outputImage(string $filePath): void
    {
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }
}
