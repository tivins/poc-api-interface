<?php

/**
 * Example: DTO with multiple sources (User + Profile) and extra properties.
 * Run: php public/example-two-sources.php
 * Generated files: GetSessionRequest, GetSessionResponse, GetSessionHandlerInterface (in src/Generated/).
 */

use Tivins\FAPI\APIInterfaceWriter;
use Tivins\FAPI\DTO;
use Tivins\FAPI\DTOExtraProperty;
use Tivins\FAPI\DTOSource;
use Tivins\FAPI\HTTPCode;
use Tivins\FAPI\OpenAPI\OpenAPI;
use Tivins\FAPI\Route;

require_once __DIR__ . '/../vendor/autoload.php';

readonly class User
{
    public function __construct(
        public int $id = 0,
        public string $name = '',
        public string $email = '',
    ) {
    }
}

readonly class Profile
{
    public function __construct(
        public string $avatar = '',
        public string $bio = '',
    ) {
    }
}

$route = new Route(
    path: '/session',
    name: 'GetSession',
    request: new DTO(User::class, ['id']),
    methods: ['GET'],
    summary: 'Get current session (user + profile + token)',
    description: 'Returns user and profile data plus session token and expiry.',
    tags: ['auth'],
    responses: [
        HTTPCode::OK->value => new DTO(
            sources: [
                new DTOSource(User::class, ['id', 'name', 'email']),
                new DTOSource(Profile::class, ['avatar', 'bio']),
            ],
            extra: [
                new DTOExtraProperty('token', 'string', "''"),
                new DTOExtraProperty('expiresAt', 'int', '0'),
            ]
        ),
    ],
);

$directory = __DIR__ . '/../src/Generated';
$apiWriter = new APIInterfaceWriter($directory, 'Tivins\FAPI\Generated', $route);
$apiWriter->generate();

$openAPI = new OpenAPI([$route]);
$path = $directory . '/openapi-get-session.json';
file_put_contents($path, json_encode($openAPI->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Generated:\n";
echo "  - GetSessionRequest.php (from User, property: id)\n";
echo "  - GetSessionResponse.php (User: id,name,email + Profile: avatar,bio + token, expiresAt)\n";
echo "  - GetSessionHandlerInterface.php\n";
echo "  - {$path}\n";
