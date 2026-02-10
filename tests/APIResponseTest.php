<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\APIResponse;

class APIResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new APIResponse(200, ['OK'], ['id' => 1]);
        $this->assertSame(200, $response->code);
        $this->assertSame(['OK'], $response->messages);
        $this->assertSame(['id' => 1], $response->data);
    }

    public function testConstructorDefaults(): void
    {
        $response = new APIResponse();
        $this->assertSame(200, $response->code);
        $this->assertSame([], $response->messages);
        $this->assertNull($response->data);
    }

    public function testToArray(): void
    {
        $response = new APIResponse(201, ['Created'], ['user' => 'john']);
        $arr = $response->toArray();
        $this->assertSame(201, $arr['code']);
        $this->assertSame(['Created'], $arr['messages']);
        $this->assertSame(['user' => 'john'], $arr['data']);
    }

    public function testToJson(): void
    {
        $response = new APIResponse(200, [], ['foo' => 'bar']);
        $json = $response->toJson();
        $decoded = json_decode($json, true);
        $this->assertSame(200, $decoded['code']);
        $this->assertSame([], $decoded['messages']);
        $this->assertSame(['foo' => 'bar'], $decoded['data']);
    }
}
