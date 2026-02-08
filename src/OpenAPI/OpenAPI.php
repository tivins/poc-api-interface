<?php

namespace Tivins\FAPI\OpenAPI;

class OpenAPI
{
    private array $paths = [];
    private array $components = [];

    public function toArray(): array
    {
        return [
            "openapi" => "3.0.0",
            'paths' => [],
            'components' => [],
        ];
    }
}