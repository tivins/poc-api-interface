<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\ForbiddenResponse;
use Tivins\FAPI\GenericErrorResponse;

class ForbiddenResponseTest extends TestCase
{
    public function testExtendsGenericErrorResponse(): void
    {
        $response = new ForbiddenResponse();
        $this->assertInstanceOf(GenericErrorResponse::class, $response);
    }

    public function testMessageIsForbidden(): void
    {
        $response = new ForbiddenResponse();
        $this->assertSame('forbidden', $response->message);
    }
}
