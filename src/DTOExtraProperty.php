<?php

declare(strict_types=1);

namespace Tivins\FAPI;

readonly class DTOExtraProperty
{
    /**
     * @param string $name    Property name
     * @param string $type    PHP type (e.g. 'string', 'int', 'bool')
     * @param string $default PHP default value literal (e.g. "''", '0', 'false')
     */
    public function __construct(
        public string $name,
        public string $type = 'string',
        public string $default = "''",
    ) {
    }
}
