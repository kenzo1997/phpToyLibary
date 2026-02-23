<?php
namespace lib\container;

use ReflectionClass;
use ReflectionNamedType;

class Container {
    protected array $instances = [];

    public function set(string $id, $concrete): void {
        $this->instances[$id] = $concrete;
    }

    public function get(string $id) {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Try to auto-resolve via Reflection
        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception("Class {$id} is not instantiable");
        }

        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return new $id;
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $params[] = $this->get($type->getName());
            } else {
                throw new \Exception("Cannot resolve parameter \${$param->getName()} of {$id}");
            }
        }

        return $reflectionClass->newInstanceArgs($params);
    }
}
?>
