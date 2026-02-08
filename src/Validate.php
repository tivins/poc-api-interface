<?php

namespace Tivins\FAPI;

use Attribute;

#[Attribute]
class Validate
{
    /**
     * @var Validator[] $validators
     */
    public array $validators;

    public function __construct(
        Validator ...$validators
    )
    {
        $this->validators = $validators;
    }
}