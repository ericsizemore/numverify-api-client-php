Numverify API Client Library for PHP
====================================

Numverify phone number validation and country API client library for PHP.

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/?branch=master)
[![Tests](https://github.com/ericsizemore/numverify-api-client-php/actions/workflows/tests.yml/badge.svg)](https://github.com/ericsizemore/numverify-api-client-php/actions/workflows/tests.yml)
[![PHPStan](https://github.com/ericsizemore/numverify-api-client-php/actions/workflows/analysis.yml/badge.svg)](https://github.com/ericsizemore/numverify-api-client-php/actions/workflows/analysis.yml)

[![Latest Stable Version](https://img.shields.io/packagist/v/esi/numverify-api-client-php.svg)](https://packagist.org/packages/esi/numverify-api-client-php)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/numverify-api-client-php.svg)](https://packagist.org/packages/esi/numverify-api-client-php)
[![License](https://img.shields.io/packagist/l/esi/numverify-api-client-php.svg)](https://packagist.org/packages/esi/numverify-api-client-php)

This library is a fork of [`markrogoyski/numverify-api-client-php`](https://github.com/markrogoyski/numverify-api-client-php). See [Acknowledgements](#scknowledgements) for more information.

## Features

* Cache of client (Guzzle) calls. See [`Construct API to use a cache`](#construct-api-to-use-a-cache)
* Phone number validation API
  * Validate phone numbers
  * Carrier information
  * Line type
  * Location info: country, local information
  * Phone number formats
* Countries API
  * List of countries
  * Country names, country codes, dialing codes
   
Numverify API documentation: https://numverify.com/documentation

## Setup

Add the library to your `composer.json` file in your project:

```javascript
{
  "require": {
      "esi/numverify-api-client-php": "3.*"
  }
}
```

Use [composer](http://getcomposer.org) to install the library:

```bash
$ php composer.phar install
```

Composer will install Numverify API Client Library for PHP inside your vendor folder. Then you can add the following to your
.php files to the use library with Autoloading.

```php
require_once __DIR__ . '/vendor/autoload.php';
```

Alternatively, use composer on the command line to require and install Numverify API Client Library:

```
$ php composer.phar require esi/numverify-api-client-php:3.*
```

### Minimum Requirements
* PHP 8.2 with `ext-json`.

Note:

* For PHP 7.2, use v2.0 of the original library (`require markrogoyski/numverify-api-client-php:2.*`)
* For PHP 7.0 and 7.1, use v1.0 of the original library (`require markrogoyski/numverify-api-client-php:1.*`)

## Usage

### Create New API
```php
$accessKey = 'AccountAccessKeyGoesHere';
$api       = new \Numverify\Api($accessKey);
```
 
### Phone Number Validation API
```php
$phoneNumber          = '14158586273';
$validatedPhoneNumber = $api->validatePhoneNumber($phoneNumber);
 
// Phone number information
if ($validatedPhoneNumber->isValid()) {
    $number              = $validatedPhoneNumber->getNumber();              // 14158586273
    $localFormat         = $validatedPhoneNumber->getLocalFormat();         // 4158586273
    $internationalPrefix = $validatedPhoneNumber->getInternationalFormat(); // +14158586273
    $countryPrefix       = $validatedPhoneNumber->getCountryPrefix();       // +1
    $countryCode         = $validatedPhoneNumber->getCountryCode();         // US
    $countryName         = $validatedPhoneNumber->getCountryName();         // United States of America
    $location            = $validatedPhoneNumber->getLocation();            // Novato
    $carrier             = $validatedPhoneNumber->getCarrier();             // AT&T Mobility LLC
    $lineType            = $validatedPhoneNumber->getLineType();            // mobile
}

// Use optional country code parameter for local (non-E.164) phone numbers
$phoneNumber = '4158586273';
$countryCode = 'US';
$validatedPhoneNumber = $api->validatePhoneNumber($phoneNumber, $countryCode);
 
// PHP Interfaces
$stringRepresentation = (string) $validatedPhoneNumber;
$jsonRepresentation   = json_encode($validatedPhoneNumber);
``` 
 
### Countries API
```php
$countries = $api->getCountries();
 
// Find countries (by country code or by name)
$unitedStates = $countries->findByCountryCode('US');
$japan        = $countries->findByCountryName('Japan');
 
// Country information
$usCountryCode = $unitedStates->getCountryCode(); // US
$usCountryName = $unitedStates->getCountryName(); // United States
$usDialingCode = $unitedStates->getDialingCode(); // +1
 
$japanCountryCode = $japan->getCountryCode();     // JP
$japanCountryName = $japan->getCountryName();     // Japan
$japanDialingCode = $japan->getDialingCode();     // +81
 
// Country collection is iterable
foreach ($countries as $country) {
    $countryCode = $country->getCountryCode();
    $countryName = $country->getCountryName();
    $dialingCode = $country->getDialingCode();
}
 
// Country collection PHP interfaces
$numberOfCountries  = count($countries);
$jsonRepresentation = json_encode($numberOfCountries);
 
// Country PHP interfaces
$stringRepresentation = (string) $unitedStates;   // US: United States (+1)
$jsonRepresentation   = json_encode($unitedStates);
```

### Options

#### Signature of the Api constructor
```php
    /**
     * Api constructor.
     *
     * Requires an access (or api) key. You can get one from Numverify:
     *
     * @see https://numverify.com/product
     *
     * Note: If you are on their free plan, $useHttps = true will not work for you.
     *
     * @param  string                $accessKey  API access key.
     * @param  bool                  $useHttps   (optional) Flag to determine if API calls should use http or https.
     * @param  ClientInterface|null  $client     (optional) Parameter to provide your own Guzzle client.
     * @param  array<string, mixed>  $options    (optional) Array of options to pass to the Guzzle client.
     */
    public function __construct(
        #[SensitiveParameter]
        private readonly string $accessKey,
        bool $useHttps = false,
        ?ClientInterface $client = null,
        array $options = []
    );
```

#### Construct API to use HTTPS for API calls

Note: The Numverify api has different plan options when signing up for an access key. The 'free' plan can not use the secure (HTTPS) url for the API.

```php
$useHttps = true;
$api      = new \Numverify\Api($accessKey, $useHttps);  // Optional second parameter
```

#### Construct API to use a custom Guzzle client or options

Note: If creating and passing your own client to `Api`, it will completely ignore `$useHttps`.

```php
$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://apilayer.net/api',
    'timeout => 10
]);

$api = new \Numverify\Api($accessKey, $useHttps, $client);
```

If you simply want to change some of Guzzle's default options, pass them along to the optional $options parameter instead.

```php
$api = new \Numverify\Api($accessKey, $useHttps, null, ['timeout => 10]);

// or
$api = new \Numverify\Api($accessKey, $useHttps, options: ['timeout => 10]);

```

#### Construct API to use a cache

The Api constructor allows you to pass an optional `$options` parameter, typically used to pass Guzzle options on to the client.

If you specify `cachePath` within `$options`, and it is a valid directory, the constructor will add the cache handler to Guzzle's handler stack.

```php
$api = new \Numverify\Api($accessKey, $useHttps, options: ['cachePath' => '/tmp']);
```

### Exceptions

API failures throw a ```NumverifyApiFailureException```

```php
// Numverify API server error
try {
    $validatedPhoneNumber = $api->validatePhoneNumber($phoneNumber);
} catch (\Numverify\Exception\NumverifyApiFailureException $e) {
    $statusCode = $e->getStatusCode(); // 500
    $message    = $e->getMessage();    // Unknown error - 500 Internal Server Error
}

// Numverify API failure response
try {
    $validatedPhoneNumber = $api->validatePhoneNumber($phoneNumber);
} catch (\Numverify\Exception\NumverifyApiFailureException $e) {
    $statusCode = $e->getStatusCode(); // 200
    $message    = $e->getMessage();    // Type:invalid_access_key Code:101 Info:You have not supplied a valid API Access Key.
}
```

## About

### Requirements

- PHP 8.2.0 or above.

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/numverify-api-client-php/issues)

Issues are the quickest way to report a bug. If you find a bug or documentation error, please check the following first:

* That there is not an Issue already open concerning the bug
* That the issue has not already been addressed (within closed Issues, for example)

### Contributing

Contributions of code and documentation from the community is welcome. 
These contributions can be made in the form of Issues or [Pull Requests](http://help.github.com/send-pull-requests/) on the [Numverify API for PHP repository](https://github.com/ericsizemore/numverify-api-client-php).

Numverify API Client for PHP is licensed under the MIT license. When submitting new features or patches to this library, you are giving permission to license those features or patches under the MIT license.

Numverify API Client for PHP tries to adhere to PHPStan level 9 with strict rules and bleeding edge. Please ensure any contributions do as well.

#### Guidelines

Before we look into how, here are the guidelines. If your Pull Requests fail to pass these guidelines it will be declined and you will need to re-submit when you’ve made the changes. This might sound a bit tough, but it is required for me to maintain quality of the code-base.

#### PHP Style

Please ensure all new contributions match the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style guide. The project is not fully PSR-12 compatible, yet; however, to ensure the easiest transition to the coding guidelines, I would like to go ahead and request that any contributions follow them.

#### Documentation

If you change anything that requires a change to documentation then you will need to add it. New methods, parameters, changing default values, adding constants, etc are all things that will require a change to documentation. The change-log must also be updated for every change. Also PHPDoc blocks must be maintained.

##### Documenting functions/variables (PHPDoc)

Please ensure all new contributions adhere to:

* [PSR-5 PHPDoc](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md)
* [PSR-19 PHPDoc Tags](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md)

when documenting new functions, or changing existing documentation.

#### Branching

One thing at a time: A pull request should only contain one change. That does not mean only one commit, but one change - however many commits it took. The reason for this is that if you change X and Y but send a pull request for both at the same time, we might really want X but disagree with Y, meaning we cannot merge the request. Using the Git-Flow branching model you can create new branches for both of these features and send two requests.

### Author

Eric Sizemore - <admin@secondversion.com> - <https://www.secondversion.com>

### License

Numverify API Client for PHP is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

### Acknowledgements

This library is a `fork` of the `markrogoyski/numverify-api-client-php`(https://github.com/markrogoyski/numverify-api-client-php) library by `Mark Rogoyski`(https://github.com/markrogoyski).

To see a list of changes in this library in comparison to the original library, please see the [CHANGELOG.md](CHANGELOG.md) file.
