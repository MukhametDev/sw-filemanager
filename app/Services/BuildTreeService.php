<?php

namespace App\Services;

class BuildTreeService
{
    public function buildTree($directories, $files, $parentId = null)
    {
        $tree = [];

        foreach ($directories as $directory) {
            if ($directory->parent_id == $parentId) {
                // Добавляем директорию в массив дерева
                $children = $this->buildTree($directories, $files, $directory->id);

                $directoryData = [
                    'id' => $directory->id,
                    'name' => $directory->name,
                    'files' => [],
                    'children' => $children,
                ];

                // Добавляем файлы в эту директорию
                foreach ($files as $file) {
                    if ($file->directory_id == $directory->id) {
                        $directoryData['files'][] = [
                            'id' => $file->id,
                            'name' => $file->name,
                        ];
                    }
                }

                $tree[] = $directoryData;
            }
        }

        return $tree;
    }
}
