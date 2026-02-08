<?php

namespace Tivins\FAPI;

readonly class GenericErrorResponse
{
    public function __construct(
        public string $message = '',
    )
    {
    }
}