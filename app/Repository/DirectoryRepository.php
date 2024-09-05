<?php

namespace App\Repository;

use App\DB\Database;
use App\Models\Directory;

class DirectoryRepository
{
    private $db;
    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllDirectories()
    {
        $sql = "SELECT * FROM `directories`";
        $data = $this->db->fetchAll($sql);

        // Предполагая, что вы хотите вернуть массив объектов Directory
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
        // SQL запрос для вставки новой директории
        $sql = "INSERT INTO `directories` (`name`, `parent_id`) VALUES (:name, :parent_id)";

        // Параметры для SQL запроса
        $params = [
            ':name' => $name,
            ':parent_id' => $parentId
        ];

        // Выполнение запроса
        $this->db->execute($sql, $params);

        // Возвращаем ID последней вставленной записи
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
        // Получаем все поддиректории текущей директории
        $subdirectories = $this->getSubdirectoriesByDirectoryId($directoryId);

        // Рекурсивно удаляем содержимое всех поддиректорий
        foreach ($subdirectories as $subdirectory) {
            $this->deleteDirectoryWithContents($subdirectory['id']);
        }

        // Получаем все файлы в текущей директории
        $files = $this->getFilesByDirectoryId($directoryId);

        // Удаляем все файлы
        foreach ($files as $file) {
            // Удаление файла из файловой системы
            if (file_exists($file['path'])) {
                if (!unlink($file['path'])) {
                    error_log("Failed to delete file: " . $file['path']);
                }
            } else {
                error_log("File not found: " . $file['path']);
            }
            // Удаление записи о файле из базы данных
            $this->deleteFile($file['id']);
        }

        // Удаляем текущую директорию
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