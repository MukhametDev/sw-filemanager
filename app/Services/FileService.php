<?php

namespace App\Services;

use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\FileRepositoryInterface;
use App\Interfaces\FileServiceInterface;
use App\Utils\FileManager;


class FileService implements FileServiceInterface
{
    private FileRepositoryInterface $fileRepository;
    private DirectoryServiceInterface $directoryService;
    private FileManager $fileManager;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        DirectoryServiceInterface $directoryService,
        FileManager $fileManager
    ) {
        $this->fileRepository = $fileRepository;
        $this->directoryService = $directoryService;
        $this->fileManager = $fileManager;
    }

    public function uploadFile(array $file, int $parentId, string $baseUploadDir): void
    {
        $parentPath = $this->directoryService->getDirectoryPathById($parentId);
        $fullUploadPath = $baseUploadDir . ($parentPath ? '/' . $parentPath : '');
        $filePath = $this->fileManager->uploadFile($file, $fullUploadPath);

        $this->fileRepository->saveFile(basename($filePath), $parentId, $file['size'], $file['type'], $filePath);
    }

    public function deleteFile(int $fileId): void
    {
        $file = $this->fileRepository->getFileById($fileId);

        $this->fileManager->checkFileExists($file);
        $this->fileManager->deleteFile($file);

        $this->fileRepository->deleteFile($fileId);
    }

    public function getFileById(int $fileId): ?array
    {
        return $this->fileRepository->getFileById($fileId);
    }
}
