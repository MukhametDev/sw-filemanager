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
        $directoryService = new DirectoryService();
        $this->fileService = new FileService($directoryService);
        $this->directoryService = $directoryService;
    }

    public function uploadFile()
    {
        $file = $_FILES['file'] ?? null;
        $parentId = $_POST['parentId'] ?? null;

        if (!$file || !$parentId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'File or Parent ID not provided'
            ]);
            return;
        }

        try {
            // Валидация файла
            FileValidator::validateFile($file);

            // Сохранение файла через сервис
            $this->fileService->uploadFile($file, $parentId);

            // Возвращаем обновленное дерево директорий и файлов
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
        $fileId = $data['id'] ?? null;

        if (!$fileId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File ID not provided']);
            return;
        }

        try {
            $this->fileService->deleteFile($fileId);

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
        // Получаем идентификатор файла из запроса
        $fileId = $_GET['id'] ?? null;

        if (!$fileId) {
            header("HTTP/1.0 400 Bad Request");
            echo "File ID not provided";
            return;
        }

        try {
            // Получаем информацию о файле из сервиса
            $file = $this->fileService->getFileById($fileId);

            if (!$file) {
                header("HTTP/1.0 404 Not Found");
                echo "File not found";
                return;
            }

            // Получаем путь к файлу
            $filePath = __DIR__ . '/../../../storage/uploads/' . $file['path']; // Предполагаем, что 'path' содержит относительный путь
            $relativeFilePath = str_replace('/var/www/storage/uploads/', '', $filePath);

            // Проверяем существование файла
            if (!file_exists($relativeFilePath)) {
                header("HTTP/1.0 404 Not Found");
                echo "File does not exist";
                return;
            }

            // Получаем MIME-тип файла
            $mimeType = mime_content_type($relativeFilePath);

            // Отправляем заголовки
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($relativeFilePath));

            // Добавляем заголовки для кэширования
            header('Cache-Control: public, max-age=86400'); // Кэширование на 1 день
            header('Pragma: public');

            // Читаем файл и выводим его
            readfile($relativeFilePath);
            exit;
        } catch (\Exception $e) {
            header("HTTP/1.0 500 Internal Server Error");
            echo "Error: " . $e->getMessage();
        }
    }
}
