<?php

namespace App\Http;

use ReflectionClass;
use ReflectionException;

class Container
{
    protected array $instances = [];

    public function get(string $class)
    {
        // Проверяем, существует ли экземпляр класса в контейнере
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        // Используем рефлексию для создания экземпляра класса
        try {
            $reflectionClass = new ReflectionClass($class);

            // Проверяем, есть ли у класса конструктор
            $constructor = $reflectionClass->getConstructor();

            // Если конструктора нет, создаём объект без параметров
            if (is_null($constructor)) {
                $object = new $class();
            } else {
                // Получаем параметры конструктора
                $parameters = $constructor->getParameters();
                $dependencies = $this->resolveDependencies($parameters);
                $object = $reflectionClass->newInstanceArgs($dependencies);
            }

            // Сохраняем экземпляр в контейнер
            $this->instances[$class] = $object;

            return $object;
        } catch (ReflectionException $e) {
            throw new \Exception("Ошибка создания экземпляра класса: " . $e->getMessage());
        }
    }

    protected function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            // Проверяем, имеет ли параметр тип класса
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                // Рекурсивно разрешаем зависимости для типа
                $dependencies[] = $this->get($type->getName());
            } else {
                throw new \Exception("Не удалось разрешить параметр {$parameter->name}");
            }
        }

        return $dependencies;
    }
}
