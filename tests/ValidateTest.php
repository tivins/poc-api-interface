<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\Validate;
use Tivins\FAPI\Validator;

class ValidateTest extends TestCase
{
    public function testConstructorSingleValidator(): void
    {
        $attr = new Validate(Validator::Email);
        $this->assertSame([Validator::Email], $attr->validators);
    }

    public function testConstructorMultipleValidators(): void
    {
        $attr = new Validate(Validator::Email, Validator::NotEmpty);
        $this->assertCount(2, $attr->validators);
        $this->assertSame(Validator::Email, $attr->validators[0]);
        $this->assertSame(Validator::NotEmpty, $attr->validators[1]);
    }
}
