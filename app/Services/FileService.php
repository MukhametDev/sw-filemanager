<?php

namespace App\Services;

use App\Repository\FileRepository;

class FileService
{
    protected $fileRepository;
    protected $directoryService;

    public function __construct(DirectoryService $directoryService)
    {
        $this->fileRepository = new FileRepository();
        $this->directoryService = $directoryService;
    }

    public function uploadFile(array $file, int $parentId): void
    {
        // Допустимые типы файлов
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception("Недопустимый тип файла");
        }

        // Ограничение по размеру файла (20MB)
        if ($file['size'] > 20000000) {
            throw new \Exception("Размер файла превышает 20MB");
        }

        // Базовая директория для загрузки файлов
        $baseUploadDir = realpath(__DIR__ . '/../../storage/uploads');
        if (!$baseUploadDir) {
            throw new \Exception("Базовая директория для загрузки не найдена");
        }

        // Получение пути к родительской директории (динамически)
        $parentPath = $this->directoryService->getDirectoryPathById($parentId);

        // Построение полного пути для загрузки
        $fullUploadPath = $baseUploadDir . ($parentPath ? '/' . $parentPath : '');

        // Убедимся, что директория существует или создаём её
        if (!is_dir($fullUploadPath)) {
            if (!mkdir($fullUploadPath, 0777, true)) {
                throw new \Exception("Ошибка при создании директории для загрузки: " . $fullUploadPath);
            }
        }

        // Генерация уникального имени файла, чтобы избежать коллизий
        $uniqueFileName = uniqid() . '_' . basename($file['name']);
        $filePath = $fullUploadPath . '/' . $uniqueFileName;

        // Загрузка файла на сервер
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception("Ошибка при загрузке файла");
        }
        $directories = $this->directoryService->getAllDirectoriesAndFiles();
        $files = $this->fileRepository->getAllFiles();
        // Сохранение информации о файле в базе данных
        $this->fileRepository->saveFile($uniqueFileName, $parentId, $file['size'], $file['type'], $filePath);

        // Возвращаем успех операции (если требуется)
//        echo json_encode([
//            'success' => true,
//            'directories' => $directories,
//            'files' => $files
//        ]);
    }

    public function deleteFile(int $fileId): void
    {
        $file = $this->fileRepository->getFileById($fileId);
        if (!$file) {
            throw new \Exception("Файл не найден");
        }

        if (file_exists($file['path'])) {
            unlink($file['path']);
        }

        $this->fileRepository->deleteFile($fileId);
    }

    public function getFileById(int $fileId): ?array
    {
        return $this->fileRepository->getFileById($fileId);
    }
}
