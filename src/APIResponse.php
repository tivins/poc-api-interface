<?php

declare(strict_types=1);
namespace Tivins\FAPI;

readonly class APIResponse
{
    public function __construct(
        public int $code = 200,
        public array $messages = [],
        public mixed $data = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'messages' => $this->messages,
            'data' => $this->data,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}