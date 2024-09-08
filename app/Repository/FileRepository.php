<?php

namespace App\Repository;

use App\DB\Database;
use App\Models\FileModel;

class FileRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getFileById(int $id): ?array
    {
        $sql = "SELECT * FROM `files` WHERE `id` = :id";
        $data = $this->db->fetchAssoc($sql, [':id' => $id]);

        return $data ?: null;
    }

    public function getAllFiles(): array
    {
        $sql = "SELECT * FROM `files`";
        $data = $this->db->fetchAll($sql);

        $files = [];
        foreach ($data as $row) {
            $files[] = new FileModel(
                $row['id'],
                $row['name'],
                $row['directory_id'],
                $row['size'],
                $row['mime_type']
            );
        }
        return $files;
    }

    public function saveFile(string $name, int $directoryId, int $size, string $mimeType, string $path): void
    {
        $sql = "INSERT INTO `files` (`name`, `directory_id`, `size`, `mime_type`, `path`) VALUES (:name, :directory_id, :size, :mime_type, :path)";
        $params = [
            ':name' => $name,
            ':directory_id' => $directoryId,
            ':size' => $size,
            ':mime_type' => $mimeType,
            ':path' => $path
        ];

        $this->db->execute($sql, $params);
    }

    public function deleteFile(int $fileId): void
    {
        $sql = "DELETE FROM `files` WHERE `id` = :id";
        $this->db->execute($sql, [':id' => $fileId]);
    }

    public function getFilesByDirectoryId(int $directoryId): array
    {
        $query = "SELECT * FROM files WHERE directory_id = :directory_id";
        return $this->db->fetchAll($query, [':directory_id' => $directoryId]);
    }
}
