<?php

declare(strict_types=1);

namespace Tivins\FAPI;

readonly class DTOSource
{
    /**
     * @param string        $class      FQCN of the class to read properties from
     * @param array<string> $properties Property names to include (order preserved)
     */
    public function __construct(
        public string $class,
        public array $properties = [],
    ) {
    }
}
