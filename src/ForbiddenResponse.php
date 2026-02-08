<?php

namespace Tivins\FAPI;

readonly class ForbiddenResponse extends \Tivins\FAPI\GenericErrorResponse
{
    public function __construct()
    {
        parent::__construct('forbidden');
    }
}