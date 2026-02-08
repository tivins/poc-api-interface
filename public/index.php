<?php

use Tivins\FAPI\APIInterfaceWriter;
use Tivins\FAPI\DTO;
use Tivins\FAPI\ForbiddenResponse;
use Tivins\FAPI\Generated\LoginHandlerInterface;
use Tivins\FAPI\Generated\LoginRequest;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\OpenAPI\OpenAPI;
use Tivins\FAPI\Route;
use Tivins\FAPI\Validate;
use Tivins\FAPI\Validator;

require_once __DIR__ . '/../vendor/autoload.php';

readonly class LoginResponse
{
    public function __construct(
        public int    $id = 0,
        public string $name = '',
        public string $email = '',
    )
    {
    }
}

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
    required: ['email' => 'string', 'password' => 'string'],
    summary: 'Login to the system',
    description: 'Login to the system',
    tags: ['auth'],
    responses: [
        HTTPCode::OK->value => LoginResponse::class,
        HTTPCode::Forbidden->value => ForbiddenResponse::class
    ],
    security: [
        'bearer' => ['Authorization' => 'Bearer {token}'],
    ],
);


$apiWriter = new APIInterfaceWriter(__dir__ .'/../src/Generated', 'Tivins\FAPI\Generated', $routes[0]);
$apiWriter->generate();

$openAPI = new OpenAPI($routes);
echo json_encode($openAPI->toArray(), JSON_PRETTY_PRINT);


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

/*
#[\Tivins\FAPI\Route(
    path: '/login',
    name: 'Login',
    methods: ['POST'],
    required: ['email' => 'string', 'password' => 'string'],
    summary: 'Login to the system',
    description: 'Login to the system',
    tags: ['auth'],
    responses: [
        HTTPCode::OK->value => LoginResponse::class,
        HTTPCode::Forbidden->value => ForbiddenResponse::class
    ],
    security: [
        'bearer' => ['Authorization' => 'Bearer {token}'],
    ],
)]
abstract class LoginHandlerInterface
{
    public function handle(array $data): ForbiddenResponse|LoginResponse
    {
        $code = $this->handleLogin(new LoginRequest($data['email'], $data['password']));
        return match ($code) {
            HTTPCode::OK => $this->returnSuccess(),
            HTTPCode::Forbidden => $this->returnForbidden(),
            HTTPCode::InternalServerError => new \Tivins\FAPI\GenericErrorResponse('unexpected error'),
        };
    }

    abstract public function handleLogin(LoginRequest $request): HTTPCode;

    abstract public function returnSuccess(): LoginResponse;

    abstract public function returnForbidden(): ForbiddenResponse;
}

$apiOut = (new LoginHandler())->handle(['email' => 'example@example.com', 'password' => 'password']);
*/
