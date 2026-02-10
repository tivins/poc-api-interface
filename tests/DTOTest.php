<?php

declare(strict_types=1);

namespace Tivins\FAPI\Tests;

use PHPUnit\Framework\TestCase;

/** Stub for DTO tests: class with public $id */
readonly class StubWithId
{
    public function __construct(public int $id = 0) {}
}
use Tivins\FAPI\DTO;
use Tivins\FAPI\DTOExtraProperty;
use Tivins\FAPI\DTOSource;

class DTOTest extends TestCase
{
    public function testConstructorLegacy(): void
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

    public function testResolvePropertiesLegacy(): void
    {
        $dto = new DTO(\stdClass::class, []);
        $resolved = $dto->resolveProperties();
        $this->assertSame([], $resolved);
    }

    public function testExtendedModeSourcesAndExtra(): void
    {
        $dto = new DTO(
            sources: [
                new DTOSource(\stdClass::class, []),
                new DTOSource(\stdClass::class, []),
            ],
            extra: [
                new DTOExtraProperty('token', 'string', "''"),
                new DTOExtraProperty('expiresAt', 'int', '0'),
            ]
        );
        $this->assertNull($dto->class);
        $names = $dto->getPropertyNames();
        $this->assertSame(['token', 'expiresAt'], $names);
        $resolved = $dto->resolveProperties();
        $this->assertCount(2, $resolved);
        $this->assertSame('token', $resolved[0]['name']);
        $this->assertSame('string', $resolved[0]['type']);
        $this->assertSame("''", $resolved[0]['default']);
        $this->assertNull($resolved[0]['reflection']);
        $this->assertSame('expiresAt', $resolved[1]['name']);
        $this->assertSame('int', $resolved[1]['type']);
        $this->assertSame('0', $resolved[1]['default']);
    }

    public function testResolvePropertiesOrder(): void
    {
        $dto = new DTO(
            sources: [
                new DTOSource(\stdClass::class, []),
            ],
            extra: [
                new DTOExtraProperty('first', 'string', "''"),
                new DTOExtraProperty('second', 'string', "''"),
            ]
        );
        $resolved = $dto->resolveProperties();
        $this->assertSame(['first', 'second'], array_column($resolved, 'name'));
    }

    public function testDuplicatePropertyNameForbidden(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate DTO property name: id');
        $dto = new DTO(
            sources: [
                new DTOSource(StubWithId::class, ['id']),
                new DTOSource(StubWithId::class, ['id']),
            ]
        );
        $dto->resolveProperties();
    }

    public function testDuplicatePropertyNameExtraAndSourceForbidden(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate DTO property name: token');
        $dto = new DTO(
            sources: [new DTOSource(\stdClass::class, [])],
            extra: [
                new DTOExtraProperty('token', 'string', "''"),
                new DTOExtraProperty('token', 'string', "''"),
            ]
        );
        $dto->resolveProperties();
    }

    public function testGetPropertyNamesExtended(): void
    {
        $dto = new DTO(
            sources: [new DTOSource(\stdClass::class, ['a', 'b'])],
            extra: [new DTOExtraProperty('c', 'string', "''")]
        );
        $this->assertSame(['a', 'b', 'c'], $dto->getPropertyNames());
    }
}
