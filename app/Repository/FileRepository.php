<?php

namespace App\Repository;

use App\DB\Database;
use App\Models\Directory;
use App\Models\FileModel;

class FileRepository
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getFileById($id): array | bool | null
    {
        $sql = "SELECT * FROM `files` WHERE `id` = :id";
        $data = $this->db->fetchAssoc($sql, [':id' => $id]);

        return $data ? $data : null;
    }
    public function getAllFiles(): ?array
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

    public function saveFile($name, $directoryId, $size, $mimeType, $path)
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

    public function deleteFile($fileId)
    {
        $sql = "DELETE FROM `files` WHERE `id` = :id";
        $this->db->execute($sql, [':id' => $fileId]);
    }
}
