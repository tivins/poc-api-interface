<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\DTO;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\Route;

class RouteTest extends TestCase
{
    public function testConstructor(): void
    {
        $request = new DTO('User', ['email', 'password']);
        $route = new Route(
            path: '/login',
            name: 'Login',
            request: $request,
            methods: ['POST'],
            summary: 'Login',
            description: 'Login to the system',
            tags: ['auth'],
            responses: [HTTPCode::OK->value => new DTO('User', ['id', 'email'])]
        );

        $this->assertSame('/login', $route->path);
        $this->assertSame('Login', $route->name);
        $this->assertSame($request, $route->request);
        $this->assertSame(['POST'], $route->methods);
        $this->assertSame('Login', $route->summary);
        $this->assertSame('Login to the system', $route->description);
        $this->assertSame(['auth'], $route->tags);
        $this->assertCount(1, $route->responses);
        $this->assertSame([], $route->required);
        $this->assertSame([], $route->optional);
        $this->assertSame([], $route->security);
    }

    public function testConstructorWithDefaults(): void
    {
        $route = new Route(path: '/health', name: 'Health', methods: ['GET']);
        $this->assertNull($route->request);
        $this->assertSame('', $route->summary);
        $this->assertSame('', $route->description);
        $this->assertSame([], $route->tags);
        $this->assertSame([], $route->responses);
    }
}
