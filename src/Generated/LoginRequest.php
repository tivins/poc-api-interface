<?php

declare(strict_types=1);

namespace Tivins\FAPI\Generated;

use Tivins\FAPI\Validate;
use Tivins\FAPI\Validator;

readonly class LoginRequest
{
    public function __construct(
        #[Validate(Validator::Email)]
        public string $email = '',
        #[Validate(Validator::NotEmpty)]
        public string $password = '',
    )
    {
    }
}

