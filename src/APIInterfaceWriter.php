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

    private function buildUses(): array
    {
        $uses = ['use ' . HTTPCode::class . ";"];
        foreach ($this->route->responses as $code => $response) {
            $uses[] = "use $response;";
        }
        return $uses;
    }

    public function getResponsesTypes(): array
    {
        $responsesTypes = [];
        foreach ($this->route->responses as $code => $response) {
            $responsesTypes[] = basename($response);
        }
        return $responsesTypes;
    }

    public function generate(): string
    {
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
PHP . "\n";

        file_put_contents("{$this->directory}/{$interfaceName}.php", $class);
        return $class;
    }

    private function getStubMethods(): array {
        $php = [];
        $php[] = "abstract public function handle{$this->route->name}(): HTTPCode;";
        /**
         * @var int $code
         * @var string $response
         */
        foreach ($this->route->responses as $code => $response) {
            $php[] = "abstract public function return" . HTTPCode::tryFrom($code)->name . "(): " . basename($response) . ";";
        }
        return $php;
    }

    private function getHandleMethod(): string {
        $responsesTypes = $this->getResponsesTypes();
        $tab = '    ';
        $class = '';
        $class .= "{$tab}public function handle(array \$data): " . implode('|', array_unique($responsesTypes)) . " {\n";
        $class .= "{$tab}{$tab}\$code = \$this->handle{$this->route->name}(new {$this->route->name}Request());\n";
        $class .= "{$tab}{$tab}return match (\$code) {\n";
        foreach ($this->route->responses as $code => $response) {
            $class .= "{$tab}{$tab}{$tab}HTTPCode::" . HTTPCode::tryFrom($code)->name . " => \$this->return" . HTTPCode::tryFrom($code)->name . "(),\n";
        }
        $class .= "{$tab}{$tab}};\n"; // end of match()
        $class .= "$tab}\n";// handle()
        return $class;
    }
}