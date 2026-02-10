<?php

declare(strict_types=1);

namespace Tivins\FAPI\Generated;

readonly class GetSessionResponse
{
    public function __construct(
        public int $id = 0,
        public string $name = '',
        public string $email = '',
        public string $avatar = '',
        public string $bio = '',
        public string $token = '',
        public int $expiresAt = 0,
    )
    {
    }
}

