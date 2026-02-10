<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\GenericErrorResponse;

class GenericErrorResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new GenericErrorResponse('Something went wrong');
        $this->assertSame('Something went wrong', $response->message);
    }

    public function testConstructorDefaultMessage(): void
    {
        $response = new GenericErrorResponse();
        $this->assertSame('', $response->message);
    }
}
