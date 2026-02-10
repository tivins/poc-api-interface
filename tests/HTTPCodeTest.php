<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\HTTPCode;

class HTTPCodeTest extends TestCase
{
    public function testOkValue(): void
    {
        $this->assertSame(200, HTTPCode::OK->value);
    }

    public function testCreatedValue(): void
    {
        $this->assertSame(201, HTTPCode::Created->value);
    }

    public function testForbiddenValue(): void
    {
        $this->assertSame(403, HTTPCode::Forbidden->value);
    }

    public function testTryFrom(): void
    {
        $this->assertSame(HTTPCode::OK, HTTPCode::tryFrom(200));
        $this->assertSame(HTTPCode::Forbidden, HTTPCode::tryFrom(403));
        $this->assertNull(HTTPCode::tryFrom(999));
    }
}
