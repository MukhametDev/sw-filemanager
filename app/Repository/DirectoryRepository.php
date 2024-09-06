<?php

namespace App\Repository;

use App\DB\Database;
use App\Models\Directory;

class DirectoryRepository
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllDirectories()
    {
        $sql = "SELECT * FROM `directories`";
        $data = $this->db->fetchAll($sql);

        $directories = [];
        foreach ($data as $row) {
            $directories[] = new Directory(
                $row['id'],
                $row['name'],
                $row['parent_id']
            );
        }
        return $directories;
    }

    public function createDirectory($name, $parentId = null)
    {
        $sql = "INSERT INTO `directories` (`name`, `parent_id`) VALUES (:name, :parent_id)";
        $params = [
            ':name' => $name,
            ':parent_id' => $parentId
        ];
        $this->db->execute($sql, $params);

        return $this->getLastInsertId();
    }

    public function getFilesByDirectoryId($directoryId)
    {
        $sql = "SELECT * FROM `files` WHERE `directory_id` = :directory_id";
        $data = $this->db->fetchAll($sql, [':directory_id' => $directoryId]);

        return $data;
    }
    public function getDirectoryById($id)
    {
        $sql = "SELECT * FROM `directories` WHERE `id` = :id";
        $data = $this->db->fetchAssoc($sql, [':id' => $id]);

        return $data;
    }

    public function getSubdirectoriesByDirectoryId($directoryId)
    {
        $sql = "SELECT * FROM `directories` WHERE `parent_id` = :parent_id";
        $data = $this->db->fetchAll($sql, [':parent_id' => $directoryId]);

        return $data;
    }
    public function getlastInsertId()
    {
        return $this->db->getLastInsertId();
    }

    public function deleteDirectoryWithContents($directoryId)
    {
        $subdirectories = $this->getSubdirectoriesByDirectoryId($directoryId);

        foreach ($subdirectories as $subdirectory) {
            $this->deleteDirectoryWithContents($subdirectory['id']);
        }

        $files = $this->getFilesByDirectoryId($directoryId);

        foreach ($files as $file) {
            if (file_exists($file['path'])) {
                if (!unlink($file['path'])) {
                    error_log("Failed to delete file: " . $file['path']);
                }
            } else {
                error_log("File not found: " . $file['path']);
            }
            $this->deleteFile($file['id']);
        }

        $this->deleteDirectory($directoryId);
    }

    public function deleteDirectory($directoryId)
    {
        $sql = "DELETE FROM `directories` WHERE `id` = :id";
        $this->db->execute($sql, [':id' => $directoryId]);
    }

    public function deleteFile($fileId)
    {
        $sql = "DELETE FROM `files` WHERE `id` = :id";
        $this->db->execute($sql, [':id' => $fileId]);
    }
}
