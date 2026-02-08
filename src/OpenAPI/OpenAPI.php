<?php

declare(strict_types=1);

namespace Tivins\FAPI\OpenAPI;

use Tivins\FAPI\DTO;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\Route;
use Tivins\FAPI\Validate;
use Tivins\FAPI\Validator;

class OpenAPI
{
    private const OPENAPI_VERSION = '3.0.0';

    /**
     * @param array<Route> $routes
     */
    public function __construct(private array $routes)
    {
    }

    public function toArray(): array
    {
        $schemas = $this->buildComponentsSchemas();
        $paths = $this->buildPaths();

        return [
            'openapi' => self::OPENAPI_VERSION,
            'info' => [
                'title' => '',
                'version' => '1.0.0',
            ],
            'paths' => $paths,
            'components' => [
                'schemas' => $schemas,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildComponentsSchemas(): array
    {
        $schemas = [];
        foreach ($this->routes as $route) {
            if ($route->request !== null) {
                $name = $this->getRequestSchemaName($route);
                $schemas[$name] = $this->dtoToSchema($route->request);
            }
            foreach ($route->responses as $code => $response) {
                $name = $this->getResponseSchemaName($route, $code);
                if (isset($schemas[$name])) {
                    continue;
                }
                $schemas[$name] = $response instanceof DTO
                    ? $this->dtoToSchema($response)
                    : $this->classToSchema($response);
            }
        }
        return $schemas;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPaths(): array
    {
        $paths = [];
        foreach ($this->routes as $route) {
            $path = $route->path;
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            foreach ($route->methods as $method) {
                $methodKey = strtolower($method);
                $paths[$path][$methodKey] = $this->buildOperation($route);
            }
        }
        return $paths;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOperation(Route $route): array
    {
        $op = [
            'summary' => $route->summary,
            'tags' => $route->tags,
        ];
        if ($route->description !== '') {
            $op['description'] = $route->description;
        }
        if ($route->request !== null) {
            $op['requestBody'] = [
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/' . $this->getRequestSchemaName($route)],
                    ],
                ],
            ];
        }
        $op['responses'] = $this->buildResponses($route);
        if ($route->security !== []) {
            $op['security'] = $this->buildSecurity($route);
        }
        return $op;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildResponses(Route $route): array
    {
        $responses = [];
        foreach ($route->responses as $code => $response) {
            $schemaName = $this->getResponseSchemaName($route, $code);
            $responses[(string) $code] = [
                'description' => "", // todo
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/' . $schemaName],
                    ],
                ],
            ];
        }
        return $responses;
    }

    /**
     * @return array<int, array<string, array<int, mixed>>>
     */
    private function buildSecurity(Route $route): array
    {
        $list = [];
        foreach (array_keys($route->security) as $name) {
            $list[] = [$name => []];
        }
        return $list;
    }

    private function getRequestSchemaName(Route $route): string
    {
        return $route->name . 'Request';
    }

    private function getResponseSchemaName(Route $route, int $code): string
    {
        $spec = $route->responses[$code];
        if ($spec instanceof DTO) {
            $codeName = HTTPCode::tryFrom($code)?->name ?? 'Response';
            return $codeName === 'OK'
                ? $route->name . 'Response'
                : $route->name . $codeName . 'Response';
        }
        return basename((string) $spec);
    }

    /**
     * @return array<string, mixed>
     */
    private function dtoToSchema(DTO $dto): array
    {
        $properties = [];
        $required = [];
        try {
            $ref = new \ReflectionClass($dto->class);
            foreach ($dto->properties as $propName) {
                $prop = $ref->getProperty($propName);
                $properties[$propName] = $this->propertyToSchema($prop);
                $required[] = $propName;
            }
        } catch (\Throwable) {
            foreach ($dto->properties as $propName) {
                $properties[$propName] = ['type' => 'string'];
                $required[] = $propName;
            }
        }
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function classToSchema(string $class): array
    {
        $properties = [];
        $required = [];
        try {
            $ref = new \ReflectionClass($class);
            foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
                $name = $prop->getName();
                $properties[$name] = $this->propertyToSchema($prop);
                $required[] = $name;
            }
        } catch (\Throwable) {
            return ['type' => 'object', 'properties' => (object) [], 'required' => []];
        }
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyToSchema(\ReflectionProperty $prop): array
    {
        $type = $prop->getType();
        $phpType = ($type instanceof \ReflectionNamedType) ? $type->getName() : 'string';
        $schema = $this->phpTypeToSchema($phpType);

        $attrs = $prop->getAttributes(Validate::class);
        if ($attrs !== []) {
            $attr = $attrs[0]->newInstance();
            foreach ($attr->validators ?? [] as $v) {
                if ($v === Validator::Email) {
                    $schema['format'] = 'email';
                    break;
                }
            }
        }
        return $schema;
    }

    /**
     * @return array<string, string>
     */
    private function phpTypeToSchema(string $phpType): array
    {
        return match ($phpType) {
            'int', 'integer' => ['type' => 'integer'],
            'float', 'double' => ['type' => 'number'],
            'bool', 'boolean' => ['type' => 'boolean'],
            'array' => ['type' => 'array'],
            default => ['type' => 'string'],
        };
    }
}
