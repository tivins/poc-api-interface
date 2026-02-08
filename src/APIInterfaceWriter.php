<?php

namespace Tivins\FAPI;

readonly class APIInterfaceWriter
{
    public function __construct(
        private string $directory,
        private string $namespace,
        private Route  $route,
    )
    {
    }

    /** @return string|DTO */
    private function getResponseSpec(int $code)
    {
        return $this->route->responses[$code];
    }

    private function isResponseDTO(int $code): bool
    {
        return $this->getResponseSpec($code) instanceof DTO;
    }

    /** Generated name for OK is {Name}Response, for others {Name}{CodeName}Response. */
    private function getResponseClassName(int $code): string
    {
        $spec = $this->getResponseSpec($code);
        if ($spec instanceof DTO) {
            $codeName = HTTPCode::tryFrom($code)?->name ?? 'Response';
            return $codeName === 'OK'
                ? $this->route->name . 'Response'
                : $this->route->name . $codeName . 'Response';
        }
        return basename((string) $spec);
    }

    private function buildUses(): array
    {
        $uses = ['use ' . HTTPCode::class . ";"];
        foreach ($this->route->responses as $code => $response) {
            if (!$response instanceof DTO) {
                $uses[] = "use $response;";
            }
        }
        return $uses;
    }

    private function hasRequestValidateAttributes(): bool
    {
        $dto = $this->route->request;
        if ($dto === null) {
            return false;
        }
        try {
            $ref = new \ReflectionClass($dto->class);
            foreach ($dto->properties as $propName) {
                $prop = $ref->getProperty($propName);
                if ($prop->getAttributes(Validate::class) !== []) {
                    return true;
                }
            }
        } catch (\Throwable) {
        }
        return false;
    }

    private function generateRequestClassBody(): string
    {
        $dto = $this->route->request;
        if ($dto === null) {
            return '';
        }
        $requestName = $this->route->name . 'Request';
        $tab = '    ';
        $lines = [];
        try {
            $ref = new \ReflectionClass($dto->class);
            foreach ($dto->properties as $propName) {
                $prop = $ref->getProperty($propName);
                $type = $prop->getType();
                $typeName = ($type instanceof \ReflectionNamedType) ? $type->getName() : 'string';
                $attrs = $prop->getAttributes(Validate::class);
                $attrLine = '';
                if ($attrs !== []) {
                    $attr = $attrs[0]->newInstance();
                    $parts = [];
                    foreach ($attr->validators ?? [] as $v) {
                        $parts[] = 'Validator::' . $v->name;
                    }
                    if ($parts !== []) {
                        $attrLine = "{$tab}{$tab}#[Validate(" . implode(', ', $parts) . ")]\n{$tab}{$tab}";
                    }
                }
                $lines[] = "{$attrLine}public {$typeName} \${$propName} = '',";
            }
        } catch (\Throwable) {
            foreach ($dto->properties as $propName) {
                $lines[] = "{$tab}{$tab}public string \${$propName} = '',";
            }
        }
        $params = implode("\n", $lines);
        $body = "{$tab}public function __construct(\n{$params}\n{$tab})\n{$tab}{\n{$tab}}\n";
        return "readonly class {$requestName}\n{\n{$body}}\n";
    }

    private function writeRequestFile(): void
    {
        if ($this->route->request === null) {
            return;
        }
        $requestName = $this->route->name . 'Request';
        $uses = [];
        if ($this->hasRequestValidateAttributes()) {
            $uses[] = 'use ' . Validate::class . ';';
            $uses[] = 'use ' . Validator::class . ';';
        }
        $usesStr = $uses !== [] ? implode("\n", $uses) . "\n\n" : '';
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$this->namespace};

{$usesStr}{$this->generateRequestClassBody()}
PHP. "\n";
        file_put_contents("{$this->directory}/{$requestName}.php", $content);
    }

    private function generateResponseClassBody(DTO $dto, string $responseName): string
    {
        $tab = '    ';
        $lines = [];
        try {
            $ref = new \ReflectionClass($dto->class);
            foreach ($dto->properties as $propName) {
                $prop = $ref->getProperty($propName);
                $type = $prop->getType();
                $typeName = ($type instanceof \ReflectionNamedType) ? $type->getName() : 'string';
                $default = $typeName === 'int' ? '0' : "''";
                $lines[] = "{$tab}{$tab}public {$typeName} \${$propName} = {$default},";
            }
        } catch (\Throwable) {
            foreach ($dto->properties as $propName) {
                $lines[] = "{$tab}{$tab}public string \${$propName} = '',";
            }
        }
        $params = implode("\n", $lines);
        $body = "{$tab}public function __construct(\n{$params}\n{$tab})\n{$tab}{\n{$tab}}\n";
        return "readonly class {$responseName}\n{\n{$body}}\n";
    }

    private function writeResponseFiles(): void
    {
        foreach ($this->route->responses as $code => $response) {
            if (!$response instanceof DTO) {
                continue;
            }
            $responseName = $this->getResponseClassName($code);
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$this->namespace};

{$this->generateResponseClassBody($response, $responseName)}
PHP . "\n";
            file_put_contents("{$this->directory}/{$responseName}.php", $content);
        }
    }

    public function getResponsesTypes(): array
    {
        $responsesTypes = [];
        foreach ($this->route->responses as $code => $response) {
            $responsesTypes[] = $this->getResponseClassName($code);
        }
        return $responsesTypes;
    }

    public function generate(): string
    {
        $this->writeRequestFile();
        $this->writeResponseFiles();

        $tab = '    ';
        $interfaceName = $this->route->name . 'HandlerInterface';
        $uses = implode("\n", $this->buildUses());
        $stubs = $tab . implode("\n\n$tab", $this->getStubMethods()) . "\n";
        $handle = $this->getHandleMethod();

        $class = <<<PHP
<?php

declare(strict_types=1);

namespace $this->namespace;

{$uses}

abstract class $interfaceName
{
$stubs
$handle
}
PHP. "\n";

        file_put_contents("{$this->directory}/{$interfaceName}.php", $class);
        return $class;
    }

    private function getStubMethods(): array
    {
        $php = [];
        $requestParam = $this->route->request !== null
            ? "({$this->route->name}Request \$request)"
            : '()';
        $php[] = "abstract public function handle{$this->route->name}{$requestParam}: HTTPCode;";
        /**
         * @var int $code
         * @var string $response
         */
        foreach ($this->route->responses as $code => $response) {
            $php[] = "abstract public function return" . HTTPCode::tryFrom($code)->name . "(): " . $this->getResponseClassName($code) . ";";
        }
        return $php;
    }

    private function getHandleMethod(): string
    {
        $responsesTypes = $this->getResponsesTypes();
        $tab = '    ';
        $class = '';
        $class .= "{$tab}public function handle(array \$data): " . implode('|', array_unique($responsesTypes)) . " {\n";
        $requestName = $this->route->name . 'Request';
        $requestArgs = $this->getHandleRequestArgs();
        $class .= "{$tab}{$tab}\$code = \$this->handle{$this->route->name}(new {$requestName}({$requestArgs}));\n";
        $class .= "{$tab}{$tab}return match (\$code) {\n";
        foreach ($this->route->responses as $code => $response) {
            $class .= "{$tab}{$tab}{$tab}HTTPCode::" . HTTPCode::tryFrom($code)->name . " => \$this->return" . HTTPCode::tryFrom($code)->name . "(),\n";
        }
        $class .= "{$tab}{$tab}};\n"; // end of match()
        $class .= "$tab}\n";// handle()
        return $class;
    }

    private function getHandleRequestArgs(): string
    {
        $dto = $this->route->request;
        if ($dto === null) {
            return '';
        }
        $parts = [];
        foreach ($dto->properties as $propName) {
            $parts[] = "{$propName}: \$data['{$propName}']";
        }
        return implode(', ', $parts);
    }
}