<?php

declare(strict_types=1);

namespace Tivins\FAPI\Generated;

use Tivins\FAPI\HTTPCode;
use LoginResponse;
use Tivins\FAPI\ForbiddenResponse;

abstract class LoginHandlerInterface
{
    abstract public function handleLogin(LoginRequest $request): HTTPCode;

    abstract public function returnOK(): LoginResponse;

    abstract public function returnForbidden(): ForbiddenResponse;

    public function handle(array $data): LoginResponse|ForbiddenResponse {
        $code = $this->handleLogin(new LoginRequest($data['email'], $data['password']));
        return match ($code) {
            HTTPCode::OK => $this->returnOK(),
            HTTPCode::Forbidden => $this->returnForbidden(),
        };
    }

}
