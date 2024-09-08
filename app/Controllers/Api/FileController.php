<?php

namespace App\Controllers\Api;

use App\Http\Response;
use App\Services\FileService;
use App\Services\DirectoryService;
use App\Validators\FileValidator;

class FileController
{
    private FileService $fileService;
    private DirectoryService $directoryService;

    public function __construct(FileService $fileService, DirectoryService $directoryService)
    {
        $this->fileService = $fileService;
        $this->directoryService = $directoryService;
    }

    public function uploadFile(): void
    {
        $file = $_FILES['file'] ?? null;
        $parentId = $_POST['parentId'] ?? null;

        if (!$file || !$parentId) {
            Response::error('Файл или ID родителя не предоставлены', 400);
        }

        FileValidator::validateFile($file);
        $this->fileService->uploadFile($file, $parentId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        Response::success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function deleteFile(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $fileId = $data['id'] ?? null;

        if (!$fileId) {
            Response::error('ID файла не предоставлен', 400);
        }

        $this->fileService->deleteFile($fileId);

        $updatedData = $this->directoryService->getAllDirectoriesAndFiles();
        Response::success([
            'directories' => $updatedData['directories'],
            'files' => $updatedData['files']
        ]);
    }

    public function downloadFile(): void
    {
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            Response::error('ID файла не предоставлен', 400);
        }

        $file = $this->fileService->getFileById($fileId);

        if (!$file) {
            Response::error('Файл не найден', 404);
        }

        $filePath = $file['path'];
        $fileName = $file['name'];

        if (!file_exists($filePath)) {
            Response::error('Файл не существует', 404);
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
            Response::error('ID файла не предоставлен', 400);
        }

        $file = $this->fileService->getFileById($fileId);

        if (!$file) {
            Response::error('Файл не найден', 404);
        }

        $filePath = __DIR__ . '/../../../storage/uploads/' . $file['path'];
        $relativeFilePath = str_replace('/var/www/storage/uploads/', '', $filePath);

        if (!file_exists($relativeFilePath)) {
            Response::error('Файл не существует', 404);
        }

        $mimeType = mime_content_type($relativeFilePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($relativeFilePath));

        readfile($relativeFilePath);
        exit;
    }
}
