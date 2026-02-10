# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added

- Unit tests with PHPUnit 11 for `DTO`, `Route`, `HTTPCode`, `APIResponse`, `GenericErrorResponse`, `ForbiddenResponse`, `OpenAPI`, `Validate`, `Validator`.
- `composer test` script to run the test suite.
- `phpunit.xml.dist` for PHPUnit configuration.
- GitHub Actions workflow (`.github/workflows/ci.yml`) to run tests on push and pull requests to `main`.

### Fixed

- `APIResponse::__construct()`: optional parameter before required `$data` (PHP 8.4 deprecation); `$data` now defaults to `null`.
