<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\Validator;

class ValidatorTest extends TestCase
{
    public function testEmailCaseExists(): void
    {
        $this->assertInstanceOf(Validator::class, Validator::Email);
    }

    public function testNotEmptyCaseExists(): void
    {
        $this->assertInstanceOf(Validator::class, Validator::NotEmpty);
    }

    public function testAllCases(): void
    {
        $cases = Validator::cases();
        $this->assertCount(2, $cases);
        $this->assertContains(Validator::Email, $cases);
        $this->assertContains(Validator::NotEmpty, $cases);
    }
}
