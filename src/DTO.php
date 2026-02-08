<?php

namespace Tivins\FAPI;

readonly class DTO
{
    public function __construct(
        public string $class, 
        public array $properties = []
    )
    {
    }
}