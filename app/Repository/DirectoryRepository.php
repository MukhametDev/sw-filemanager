<?php

namespace App\Repository;

use App\DB\Database;
use App\Interfaces\DirectoryRepositoryInterface;
use App\Models\Directory;

class DirectoryRepository implements DirectoryRepositoryInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllDirectories(): array
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

    public function getSubdirectories(int $parentId): array
    {
        $query = "SELECT * FROM directories WHERE parent_id = :parent_id";
        return $this->db->fetchAll($query, [':parent_id' => $parentId]);
    }

    public function createDirectory($name, $parentId = null): int
    {
        $sql = "INSERT INTO `directories` (`name`, `parent_id`) VALUES (:name, :parent_id)";
        $params = [
            ':name' => $name,
            ':parent_id' => $parentId
        ];
        $this->db->execute($sql, $params);

        return $this->getLastInsertId();
    }

    public function getDirectoryById($id): ?array
    {
        $sql = "SELECT * FROM `directories` WHERE `id` = :id";
        return $this->db->fetchAssoc($sql, [':id' => $id]);
    }

    public function deleteDirectory(int $directoryId): void
    {
        $sql = "DELETE FROM `directories` WHERE `id` = :id";
        $this->db->execute($sql, [':id' => $directoryId]);
    }

    public function getLastInsertId(): int
    {
        return $this->db->getLastInsertId();
    }
}
