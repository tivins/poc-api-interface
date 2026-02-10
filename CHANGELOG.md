# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.1.0] - 2025-02-10

### Added

- Extended DTO: multiple sources and custom properties via `DTOSource` and `DTOExtraProperty`.
- `DTO::resolveProperties()`: ordered list of properties (sources then extra); duplicate property names throw `\InvalidArgumentException`.
- `DTO::getPropertyNames()`: property names in order without reflection (for fallback).
- `DTO` constructor now accepts optional `sources` and `extra`; legacy `new DTO($class, $properties)` unchanged.

### Changed

- `APIInterfaceWriter` and `OpenAPI` use `DTO::resolveProperties()` for request/response and schema generation.

## [1.0.0] - (prior)

### Added

- Unit tests with PHPUnit 11 for `DTO`, `Route`, `HTTPCode`, `APIResponse`, `GenericErrorResponse`, `ForbiddenResponse`, `OpenAPI`, `Validate`, `Validator`.
- `composer test` script to run the test suite.
- `phpunit.xml.dist` for PHPUnit configuration.
- GitHub Actions workflow (`.github/workflows/ci.yml`) to run tests on push and pull requests to `main`.

### Fixed

- `APIResponse::__construct()`: optional parameter before required `$data` (PHP 8.4 deprecation); `$data` now defaults to `null`.
