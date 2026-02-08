<?php

use Tivins\FAPI\APIInterfaceWriter;
use Tivins\FAPI\DTO;
use Tivins\FAPI\ForbiddenResponse;
use Tivins\FAPI\Generated\LoginHandlerInterface;
use Tivins\FAPI\Generated\LoginRequest;
use Tivins\FAPI\Generated\LoginResponse;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\OpenAPI\OpenAPI;
use Tivins\FAPI\Route;
use Tivins\FAPI\Validate;
use Tivins\FAPI\Validator;

require_once __DIR__ . '/../vendor/autoload.php';

readonly class User
{
    public function __construct(
        public int    $id = 0,
        public string $name = '',
        #[Validate(Validator::Email)]
        public string $email = '',
        #[Validate(Validator::NotEmpty)]
        public string $password = '',
    )
    {
    }
}

$routes = [];
$routes[] = new Route(
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
    # security: [
    #     'bearer' => ['Authorization' => 'Bearer {token}'],
    # ],
);


$apiWriter = new APIInterfaceWriter(__dir__ .'/../src/Generated', 'Tivins\FAPI\Generated', $routes[0]);
$apiWriter->generate();

$openAPI = new OpenAPI($routes);
file_put_contents(__dir__ . '/../src/Generated/openapi.json', json_encode($openAPI->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));


class LoginHandler extends LoginHandlerInterface
{
    private ?User $user = null;

    public function handleLogin(LoginRequest $request): HTTPCode
    {
        if (somethingWentWrong()) {
            return HTTPCode::Forbidden;
        }

        $this->user = new User(
            id: 1,
            name: 'John Doe',
            email: 'john.doe@example.com',
        );
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

function somethingWentWrong(): bool {
    return rand(1,10) < 5;
}


$apiOut = (new LoginHandler())->handle(['email' => 'example@example.com', 'password' => 'password']);
var_dump($apiOut);
