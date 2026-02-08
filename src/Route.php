<?php

namespace Tivins\FAPI;

use Attribute;

#[Attribute]
class Route
{
    public function __construct(
        public string $path,
        public string $name,
        public ?DTO $request = null,
        public array  $methods = [],
        public array  $required = [],
        public array  $optional = [],
        public string $summary = '',
        public string $description = '',
        public array  $tags = [],
        public array  $responses = [],
        public array  $security = [],
    )
    {
    }
}