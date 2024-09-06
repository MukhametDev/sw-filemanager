<?php

namespace App\Controllers\Api;

use App\Services\FileService;
use App\Services\DirectoryService;
use App\Validators\FileValidator;

class FileController
{
    private $fileService;
    private $directoryService;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->directoryService = new DirectoryService();
    }

    public function uploadFile()
    {
        $file = $_FILES['file'];
        $parentId = $_POST['parentId'];

        try {
            FileValidator::validateFile($file);
            $this->fileService->uploadFile($file, $parentId);

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

    public function deleteFile()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $fileId = $data['id'];

        try {
            $this->fileService->deleteFile($fileId);

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

    public function downloadFile()
    {
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            echo json_encode(['success' => false, 'error' => 'File ID not provided']);
            return;
        }

        try {
            $file = $this->fileService->getFileById($fileId);

            if (!$file) {
                echo json_encode(['success' => false, 'error' => 'File not found']);
                return;
            }

            $filePath = $file['path'];
            $fileName = $file['name'];

            if (!file_exists($filePath)) {
                echo json_encode(['success' => false, 'error' => 'File does not exist']);
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
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function showImage()
    {
        $fileName = urldecode($_GET['file']);

        $filePath = __DIR__ . '/../../../storage/uploads/' . $fileName;

        if (!file_exists($filePath)) {
            header("HTTP/1.0 404 Not Found");
            echo "File not found";
            return;
        }

        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }
}
