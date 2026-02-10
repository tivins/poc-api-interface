<?php

declare(strict_types=1);

namespace Tivins\FAPI\Generated;

use Tivins\FAPI\HTTPCode;

abstract class GetSessionHandlerInterface
{
    abstract public function handleGetSession(GetSessionRequest $request): HTTPCode;

    abstract public function returnOK(): GetSessionResponse;

    public function handle(array $data): GetSessionResponse {
        $code = $this->handleGetSession(new GetSessionRequest(id: $data['id']));
        return match ($code) {
            HTTPCode::OK => $this->returnOK(),
        };
    }

}
