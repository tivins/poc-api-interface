<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\DTO;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\OpenAPI\OpenAPI;
use Tivins\FAPI\Route;

class OpenAPITest extends TestCase
{
    public function testToArrayStructure(): void
    {
        $route = new Route(
            path: '/test',
            name: 'Test',
            methods: ['GET'],
            summary: 'Test endpoint'
        );
        $openApi = new OpenAPI([$route]);
        $arr = $openApi->toArray();

        $this->assertSame('3.0.0', $arr['openapi']);
        $this->assertArrayHasKey('info', $arr);
        $this->assertSame('1.0.0', $arr['info']['version']);
        $this->assertArrayHasKey('paths', $arr);
        $this->assertArrayHasKey('components', $arr);
        $this->assertArrayHasKey('schemas', $arr['components']);
    }

    public function testPathBuiltFromRoute(): void
    {
        $route = new Route(
            path: '/login',
            name: 'Login',
            methods: ['POST', 'GET'],
            summary: 'Login'
        );
        $openApi = new OpenAPI([$route]);
        $arr = $openApi->toArray();

        $this->assertArrayHasKey('/login', $arr['paths']);
        $this->assertArrayHasKey('post', $arr['paths']['/login']);
        $this->assertArrayHasKey('get', $arr['paths']['/login']);
        $this->assertSame('Login', $arr['paths']['/login']['post']['summary']);
    }

    public function testRequestSchemaWhenRouteHasRequest(): void
    {
        $requestDto = new DTO(\stdClass::class, ['email', 'password']);
        $route = new Route(
            path: '/login',
            name: 'Login',
            request: $requestDto,
            methods: ['POST'],
            responses: [HTTPCode::OK->value => new DTO(\stdClass::class, ['id'])]
        );
        $openApi = new OpenAPI([$route]);
        $arr = $openApi->toArray();

        $this->assertArrayHasKey('LoginRequest', $arr['components']['schemas']);
        $this->assertArrayHasKey('LoginResponse', $arr['components']['schemas']);
        $schema = $arr['components']['schemas']['LoginRequest'];
        $this->assertSame('object', $schema['type']);
        $this->assertArrayHasKey('properties', $schema);
        $this->assertContains('email', $schema['required']);
        $this->assertContains('password', $schema['required']);
    }
}
