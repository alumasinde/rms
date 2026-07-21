<?php

declare(strict_types=1);

namespace App\Core\Container;

use App\Core\Exceptions\ContainerException;
use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Minimal PSR-11-ish DI container with constructor autowiring.
 */
final class Container
{
    private static ?Container $instance = null;

    /** @var array<string, Closure> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $shared = [];

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function bind(string $abstract, Closure $concrete, bool $shared = false): void
    {
        $this->bindings[$abstract] = $concrete;
        if ($shared) {
            $this->shared[$abstract] = true;
        }
    }

    public function singleton(string $abstract, Closure $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $object = ($this->bindings[$abstract])($this);

            if (isset($this->shared[$abstract])) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        return $this->resolve($abstract);
    }

    private function resolve(string $class): object
    {
        if (!class_exists($class)) {
            throw new ContainerException("Class {$class} does not exist and no binding was registered.");
        }

        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class {$class} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = array_map(
            fn (ReflectionParameter $param) => $this->resolveParameter($param, $class),
            $constructor->getParameters()
        );

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveParameter(ReflectionParameter $param, string $forClass): mixed
    {
        $type = $param->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->make($type->getName());
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new ContainerException(
            "Cannot resolve parameter \${$param->getName()} for class {$forClass}."
        );
    }
}
