# CHANGELOG
A not so exhaustive list of changes for each release.

For a more detailed listing of changes between each version, 
you can use the following url: https://github.com/ericsizemore/api/compare/v3.0.0...v3.0.1. 

Simply replace the version numbers depending on which set of changes you wish to see.


## 3.0.1 (2024-03-13)

### Added

  * `vimeo/psalm` added into dev-dependencies and workflow
  * `TestDox` attribute used for all unit tests instead of '@testCase' (work in progress)

### Changed

  * Updates throughout to fix psalm-reported issues.
    * Initially level 2, now should be valid on Psalm level 1.
  * Changed the following private methods of the `Api` class to static methods:
    * `buildCacheHandler`
    * `validateAndDecodeResponse`
    * `getUrl`
  * Modifed `Numverify\PhoneNumber\PhoneNumberInterface` to extend `JsonSerializable`, `Stringable`
  * Refactored `validateAndDecodeResponse` and `buildCacheHandler`

### Removed

  * Removed unnecessary constructor and properties from `Numverify\Exception\NumverifyApiResponseException`
  * Removed '@testCase' annotation from all unit tests.

### TODO

  * At the moment the cache middleware for Guzzle is using files. For 3.1, perhaps I could look into supporting Redis, Memcached, etc.


## 3.0.0 (2024-03-10)

Forked from [`markrogoyski/numverify-api-client-php`](https://github.com/markrogoyski/numverify-api-client-php) v2.2.0.

### Added

  * Guzzle cache support via `kevinrob/guzzle-cache-middleware` and `symfony/cache`.
    * New function `Api::buildCacheHandler` which will add the cache middleware to the handler stack if $options['cachePath'] is passed to Api's constructor.
  * Imports for all used functions, constants, and class names.
  * dev-dependencies for PHP-CS-Fixer and PHPStan (w/extensions for phpunit, strict rules)
  * New workflow for static analysis: `.github/workflows/analysis.yml`
  * CHANGELOG.md, SECURITY.md

### Changed

  * Updated composer.json
    * Bumped minimum PHP version to 8.2
    * Autoloading should follow PSR-4
    * Updated PHPUnit to 11.0+
  * Added `strict_types` declaration and PHPDoc header to all files.
  * `Api::__construct` has a new `$options` parameter, which is optional, that can pass options to Guzzle Client.
  * `NumverifyApiFailureException` is wrapped around `\GuzzleHttp\Exception\ServerException`
  * `Api::validateResponse()` changed to `Api::validateAndDecodeResponse()`.
    * Old signature: `private function validateResponse(\Psr\Http\Message\ResponseInterface $response): void`
    * New signature: `private function validateAndDecodeResponse(ResponseInterface $response, bool $asArray = false): stdClass | array`
    * It now checks `getStatusCode` and the response body for `success`. Returns the decoded jSON.
  * Updated unit tests to use Guzzle's `MockHandler`.
    * Fixed tests and PHPUnit method calls/attributes/etc. to be in line with PHPUnit 11+
  * Cleaned up code and refactored to use newer PHP 8 features / conventions.
    * Should now adhere to PER and PSR-12 as well.
    * Now passes PHPStan using level 9 w/strict rules.
    * Added parameter and return type hints throughout.
  * Any class implementing `JsonSerializable` now also implements `Stringable`.
  * Updated README.md

### Removed

  * `.github/workflows/test_develop_and_master.yml`, replaced with `.github/workflows/tests.yml`
  * `.github/workflows/test_other_branches.yml`, replaced with `.github/workflows/tests.yml`
  * `.github/workflows/test_pull_request.yml`, replaced with `.github/workflows/tests.yml`
  * `tests/bootstrap.php`, `tests/phpunit.xml`, `tests/coding_standard.xml`

### TODO/WIP

  * Documentation improvements.