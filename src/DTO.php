<?php

declare(strict_types=1);

namespace Tivins\FAPI;

readonly class DTO
{
    /**
     * Legacy mode: use $class + $properties (when $sources and $extra are empty).
     * Extended mode: use $sources then $extra (order preserved). Duplicate property names are forbidden.
     *
     * @param string|null   $class     Legacy: FQCN to read properties from
     * @param array<string> $properties Legacy: property names
     * @param array<DTOSource> $sources  Extended: classes and their properties (order preserved)
     * @param array<DTOExtraProperty> $extra   Extended: custom properties with type and default
     */
    public function __construct(
        public ?string $class = null,
        public array $properties = [],
        public array $sources = [],
        public array $extra = [],
    ) {
    }

    /**
     * Resolve all properties in order. Forbids duplicate names.
     *
     * @return list<array{name: string, type: string, default: string, reflection: ?\ReflectionProperty}>
     * @throws \InvalidArgumentException on duplicate property name
     */
    public function resolveProperties(): array
    {
        $seen = [];
        $result = [];

        if ($this->sources !== [] || $this->extra !== []) {
            foreach ($this->sources as $source) {
                if (!$source instanceof DTOSource) {
                    continue;
                }
                $ref = new \ReflectionClass($source->class);
                foreach ($source->properties as $propName) {
                    if (isset($seen[$propName])) {
                        throw new \InvalidArgumentException("Duplicate DTO property name: {$propName}");
                    }
                    $seen[$propName] = true;
                    $prop = $ref->getProperty($propName);
                    $type = $prop->getType();
                    $typeName = ($type instanceof \ReflectionNamedType) ? $type->getName() : 'string';
                    $default = $typeName === 'int' ? '0' : "''";
                    $result[] = ['name' => $propName, 'type' => $typeName, 'default' => $default, 'reflection' => $prop];
                }
            }
            foreach ($this->extra as $ext) {
                if (!$ext instanceof DTOExtraProperty) {
                    continue;
                }
                if (isset($seen[$ext->name])) {
                    throw new \InvalidArgumentException("Duplicate DTO property name: {$ext->name}");
                }
                $seen[$ext->name] = true;
                $result[] = ['name' => $ext->name, 'type' => $ext->type, 'default' => $ext->default, 'reflection' => null];
            }
            return $result;
        }

        // Legacy: class + properties
        if ($this->class === null || $this->class === '') {
            return $result;
        }
        $ref = new \ReflectionClass($this->class);
        foreach ($this->properties as $propName) {
            if (isset($seen[$propName])) {
                throw new \InvalidArgumentException("Duplicate DTO property name: {$propName}");
            }
            $seen[$propName] = true;
            $prop = $ref->getProperty($propName);
            $type = $prop->getType();
            $typeName = ($type instanceof \ReflectionNamedType) ? $type->getName() : 'string';
            $default = $typeName === 'int' ? '0' : "''";
            $result[] = ['name' => $propName, 'type' => $typeName, 'default' => $default, 'reflection' => $prop];
        }
        return $result;
    }

    /**
     * Property names in order (no reflection). For fallback when resolution fails.
     *
     * @return list<string>
     */
    public function getPropertyNames(): array
    {
        if ($this->sources !== [] || $this->extra !== []) {
            $names = [];
            foreach ($this->sources as $source) {
                if ($source instanceof DTOSource) {
                    foreach ($source->properties as $p) {
                        $names[] = $p;
                    }
                }
            }
            foreach ($this->extra as $ext) {
                if ($ext instanceof DTOExtraProperty) {
                    $names[] = $ext->name;
                }
            }
            return $names;
        }
        return $this->properties;
    }
}
