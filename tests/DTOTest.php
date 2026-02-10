<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;
use Tivins\FAPI\DTO;

class DTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $dto = new DTO('User', ['id', 'name']);
        $this->assertSame('User', $dto->class);
        $this->assertSame(['id', 'name'], $dto->properties);
    }

    public function testConstructorWithEmptyProperties(): void
    {
        $dto = new DTO('EmptyModel');
        $this->assertSame('EmptyModel', $dto->class);
        $this->assertSame([], $dto->properties);
    }

    public function testReadonly(): void
    {
        $dto = new DTO('Test', ['a']);
        $this->assertInstanceOf(DTO::class, $dto);
    }
}
