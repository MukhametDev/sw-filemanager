<?php

namespace App\Controllers\Api;

use App\DB\Database;
use App\Repository\FileRepository;

class FileController
{
    private $fileRepository;
    public function __construct(Database $db)
    {
        $this->fileRepository = new FileRepository($db);
    }
    public function uploadFile()
    {
        $parentId = $_POST['parentId'];
        $file = $_FILES['file'];

        // Проверка типа файла и его размера
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['error' => 'Invalid file type']);
            return;
        }

        if ($file['size'] > 20000000) { // 20MB лимит
            echo json_encode(['error' => 'File size exceeds the limit of 20MB']);
            return;
        }

        // Сохранение файла в директорию на сервере
        $uploadDir = __DIR__ . '/../../../storage/uploads';
        $filePath = $uploadDir . '/' . basename($file['name']);

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // 0777 - полные права, true - рекурсивное создание директорий
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Сохранение данных о файле в базе данных
            $this->fileRepository->saveFile($file['name'], $parentId, $file['size'], $file['type'], $filePath);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'File upload failed']);
        }
    }


    public function downloadFile()
    {
        $fileId = $_GET['id'];

        // Получаем информацию о файле из базы данных
        $file = $this->fileRepository->getFileById($fileId);

        if (!$file['path']) {
            echo json_encode(['error' => 'File not found']);
            return;
        }

        // Заголовки для скачивания файла
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . basename($file['path']) . '"');
        header('Content-Length: ' . filesize($file['path']));
        header('Pragma: public');

        // Чтение файла и его вывод
        readfile($file['path']);
        exit;
    }

    public function deleteFile()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $fileId = $data['id'];

        // Получаем информацию о файле из базы данных
        $file = $this->fileRepository->getFileById($fileId);

        if (!$file) {
            echo json_encode(['error' => 'File not found']);
            return;
        }

        // Удаляем файл с сервера
        if (file_exists($file['path'])) {
            unlink($file['path']);
        } else {
            echo json_encode(['error' => 'File not found on disk']);
            return;
        }

        // Удаляем запись о файле из базы данных
        $this->fileRepository->deleteFile($fileId);

        echo json_encode(['success' => true]);
    }

    public function showImage()
    {
        $fileName = urldecode($_GET['file']);$_GET['file'];

        // Путь к файлу в директории uploads
        $filePath = __DIR__ . '/../../../storage/uploads/' . $fileName;

        // Проверяем, существует ли файл
        if (!file_exists($filePath)) {
            header("HTTP/1.0 404 Not Found");
            echo "File not found";
            return;
        }

        // Определяем MIME тип файла
        $mimeType = mime_content_type($filePath);

        // Устанавливаем заголовки
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));

        // Отдаем файл
        readfile($filePath);
        exit;
    }
}