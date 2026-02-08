<?php

namespace Tivins\FAPI\OpenAPI;

use Tivins\FAPI\Route;

class OpenAPI
{
    private array $paths = [];
    private array $components = [];

    /**
     * @param array<Route> $routes
     */
    public function __construct(private array $routes)
    {
    }

    public function toArray(): array
    {
        $data = [
            "openapi" => "3.0.0",
            'paths' => [],
            'components' => [],
        ];

        foreach ($this->routes as $route) {
            $path = $route->path;

            $routeAPI[] = [
                'summary' => $route->summary,
                'tags' => $route->tags,
                'requestBody' => ['content' => ["application/json" => ['schema' => 'todo']] ],
            ];
            $data["paths"][$path][$route->methods[0]] = $routeAPI;
        }

        return $data;
    }
}
/*{
  "openapi": "3.0.0",
  "paths": {
    "/login": {
      "post": {
        "summary": "Login to the system",
        "tags": ["auth"],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": { "$ref": "#/components/schemas/LoginRequest" }
            }
          }
        },
        "responses": {
          "200": {
            "content": {
              "application/json": {
                "schema": { "$ref": "#/components/schemas/LoginResponse" }
              }
            }
          },
          "403": {
            "content": {
              "application/json": {
                "schema": { "$ref": "#/components/schemas/ForbiddenResponse" }
              }
            }
          }
        },
        "security": [{ "bearer": [] }]
      }
    }
  },
  "components": {
    "schemas": {
      "LoginRequest": { "type": "object", "properties": { "email": { "type": "string", "format": "email" }, "password": { "type": "string" } }, "required": ["email", "password"] },
      "LoginResponse": { "type": "object", "properties": { "id": { "type": "integer" }, "name": { "type": "string" }, "email": { "type": "string" } } },
      "ForbiddenResponse": { "type": "object", "properties": { "message": { "type": "string" } } }
    }
  }
}*/