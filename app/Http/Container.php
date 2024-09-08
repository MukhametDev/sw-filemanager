<?php

namespace App\Http;

use ReflectionClass;
use ReflectionException;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $interface, string $implementation): void
    {
        $this->bindings[$interface] = $implementation;
    }

    public function get(string $class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $implementation = $this->bindings[$class] ?? $class;

        try {
            $reflectionClass = new ReflectionClass($implementation);
            $constructor = $reflectionClass->getConstructor();

            if (is_null($constructor)) {
                $object = new $implementation();
            } else {
                $parameters = $constructor->getParameters();
                $dependencies = $this->resolveDependencies($parameters);
                $object = $reflectionClass->newInstanceArgs($dependencies);
            }

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
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
            } else {
                throw new \Exception("Не удалось разрешить параметр {$parameter->name}");
            }
        }

        return $dependencies;
    }
}
