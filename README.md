# FAPI — API-first PHP PoC

A **Proof of Concept** PHP 8.3 library that lets you describe API endpoints once and get **generated request/response DTOs**, a **typed handler interface**, and an **OpenAPI 3.0** spec from the same definition.

## Idea

You define:

- **Routes**: path, HTTP methods, summary, tags, and a mapping of HTTP status codes to response types.
- **Request/response shapes**: via existing domain classes (e.g. `User`) and a `DTO` wrapper that selects which properties are used for the request or each response.
- **Validation**: using a `#[Validate(Validator::…)]` attribute on domain properties (e.g. email, not empty).

From that, the PoC **generates**:

1. **Request class** (e.g. `LoginRequest`) — readonly, with the chosen properties and validation attributes.
2. **Response classes** (e.g. `LoginResponse`, `LoginForbiddenResponse`) — one per status code when the response is a DTO.
3. **Handler interface** (e.g. `LoginHandlerInterface`) with:
   - `handleXxx(Request $request): HTTPCode` — your logic returns a status code.
   - `returnOK(): LoginResponse`, `returnForbidden(): ForbiddenResponse`, etc. — you provide the actual response objects.
   - `handle(array $data): LoginResponse|ForbiddenResponse` — builds the request, calls your handler, then returns the correct response object via `match($code)`.

You implement the handler (e.g. `LoginHandler extends LoginHandlerInterface`), and the type system guarantees that for each HTTP code you return the right response type.

The same route definitions are also used to build an **OpenAPI 3.0** JSON document (e.g. `src/Generated/openapi.json`).

## Requirements

- PHP 8.3+
- Composer

## Install

```bash
composer install
```

## Project layout

| Path | Role |
|------|------|
| `src/` | Core library: `Route`, `DTO`, `APIInterfaceWriter`, `OpenAPI`, `Validate`/`Validator`, `HTTPCode`, response base classes |
| `src/Generated/` | Generated request/response classes, handler interface, and `openapi.json` (written by the PoC) |
| `public/index.php` | Example: defines a route and a `User` DTO, runs the generator, implements `LoginHandler`, calls `handle()` |

## Usage overview

### 1. Define a domain class (optional but useful)

Use your own class for request/response shapes and attach validation where needed:

```php
readonly class User
{
    public function __construct(
        public int    $id = 0,
        public string $name = '',
        #[Validate(Validator::Email)]
        public string $email = '',
        #[Validate(Validator::NotEmpty)]
        public string $password = '',
    ) {}
}
```

### 2. Define a route

```php
use Tivins\FAPI\Route;
use Tivins\FAPI\DTO;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\ForbiddenResponse;

$route = new Route(
    path: '/login',
    name: 'Login',
    request: new DTO(User::class, ['email', 'password']),
    methods: ['POST'],
    summary: 'Login to the system',
    description: 'Login to the system',
    tags: ['auth'],
    responses: [
        HTTPCode::OK->value => new DTO(User::class, ['id', 'name', 'email']),
        HTTPCode::Forbidden->value => ForbiddenResponse::class
    ],
);
```

- **Request**: DTO from `User`, only `email` and `password`.
- **Responses**: 200 → DTO from `User` (`id`, `name`, `email`); 403 → existing class `ForbiddenResponse`.

### 3. Generate code and OpenAPI

```php
use Tivins\FAPI\APIInterfaceWriter;
use Tivins\FAPI\OpenAPI\OpenAPI;

$apiWriter = new APIInterfaceWriter(
    __DIR__ . '/../src/Generated',
    'Tivins\FAPI\Generated',
    $route
);
$apiWriter->generate();

$openAPI = new OpenAPI([$route]);
file_put_contents(
    __DIR__ . '/../src/Generated/openapi.json',
    json_encode($openAPI->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);
```

This creates/overwrites:

- `LoginRequest.php`
- `LoginResponse.php`, and optionally `LoginForbiddenResponse.php` (here 403 uses a concrete class, so only `LoginResponse` is generated)
- `LoginHandlerInterface.php`
- `openapi.json`

### 4. Implement the handler

```php
class LoginHandler extends LoginHandlerInterface
{
    private ?User $user = null;

    public function handleLogin(LoginRequest $request): HTTPCode
    {
        if (somethingWentWrong()) {
            return HTTPCode::Forbidden;
        }
        $this->user = new User(1, 'John Doe', 'john.doe@example.com', '');
        return HTTPCode::OK;
    }

    public function returnOK(): LoginResponse
    {
        return new LoginResponse(
            $this->user->id,
            $this->user->name,
            $this->user->email,
        );
    }

    public function returnForbidden(): ForbiddenResponse
    {
        return new ForbiddenResponse();
    }
}
```

### 5. Run the endpoint

```php
$handler = new LoginHandler();
$response = $handler->handle(['email' => 'example@example.com', 'password' => 'password']);
// $response is LoginResponse|ForbiddenResponse
```

No HTTP stack is included in this PoC; `public/index.php` only demonstrates the handler call and output (e.g. `var_dump($apiOut)`). In a real app you would plug this into your router and serialize the response object to JSON.

## What this PoC demonstrates

- **Single source of truth**: routes + DTOs drive both PHP types and OpenAPI.
- **Type-safe handlers**: the interface forces you to return the correct response type per HTTP code.
- **Validation**: `#[Validate]` is reflected into generated request classes and into the OpenAPI schema (e.g. `format: email`).
- **Reuse**: domain classes (e.g. `User`) can back multiple routes with different property subsets via `DTO(Class, ['prop1', 'prop2'])`.

## License

MIT.
