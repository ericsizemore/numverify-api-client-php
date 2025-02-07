Numverify API Client Library for PHP
====================================

Numverify phone number validation and country API client library for PHP.

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/numverify-api-client-php/?branch=master)
[![Continuous Integration](https://github.com/ericsizemore/numverify-api-client-php/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ericsizemore/numverify-api-client-php/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/ericsizemore/numverify-api-client-php/coverage.svg)](https://shepherd.dev/github/ericsizemore/numverify-api-client-php)
[![Psalm Level](https://shepherd.dev/github/ericsizemore/numverify-api-client-php/level.svg)](https://shepherd.dev/github/ericsizemore/numverify-api-client-php)
[![Latest Stable Version](https://img.shields.io/packagist/v/esi/numverify-api-client-php.svg)](https://packagist.org/packages/esi/numverify-api-client-php)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/numverify-api-client-php.svg)](https://packagist.org/packages/esi/numverify-api-client-php)
[![License](https://img.shields.io/packagist/l/esi/numverify-api-client-php.svg)](https://packagist.org/packages/esi/numverify-api-client-php)

This library is a fork of [`markrogoyski/numverify-api-client-php`](https://github.com/markrogoyski/numverify-api-client-php). See [Credits](#credits) for more information.

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

## Installation

Compatible with PHP >= 8.2 and can be installed with [Composer](https://getcomposer.org).

```bash
$ composer require esi/numverify-api-client-php
```

## Usage

### Create New API
```php
use Numverify\Api;

$accessKey = 'AccountAccessKeyGoesHere';
$api       = new Api($accessKey);
```
 
### Phone Number Validation API
```php
$phoneNumber          = '14158586273';
$validatedPhoneNumber = $api->validatePhoneNumber($phoneNumber);
 
// Phone number information.
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

// Use optional country code parameter for local (non-E.164) phone numbers.
$phoneNumber = '4158586273';
$countryCode = 'US';
$validatedPhoneNumber = $api->validatePhoneNumber($phoneNumber, $countryCode);
 
// PHP Interfaces.
$stringRepresentation = (string) $validatedPhoneNumber;
$jsonRepresentation   = json_encode($validatedPhoneNumber);
``` 
 
### Countries API
```php
$countries = $api->getCountries();
 
// Find countries (by country code or by name).
$unitedStates = $countries->findByCountryCode('US');
$japan        = $countries->findByCountryName('Japan');
 
// Country information.
$usCountryCode = $unitedStates->getCountryCode(); // US
$usCountryName = $unitedStates->getCountryName(); // United States
$usDialingCode = $unitedStates->getDialingCode(); // +1
 
$japanCountryCode = $japan->getCountryCode();     // JP
$japanCountryName = $japan->getCountryName();     // Japan
$japanDialingCode = $japan->getDialingCode();     // +81
 
// Country collection is iterable.
foreach ($countries as $country) {
    $countryCode = $country->getCountryCode();
    $countryName = $country->getCountryName();
    $dialingCode = $country->getDialingCode();
}
 
// Country collection PHP interfaces.
$numberOfCountries  = count($countries);
$jsonRepresentation = json_encode($numberOfCountries);
 
// Country PHP interfaces.
$stringRepresentation = (string) $unitedStates;   // US: United States (+1)
$jsonRepresentation   = json_encode($unitedStates);
```

### Options

#### Signature of the Api Constructor
```php
    /**
     * Api constructor.
     *
     * Requires an access key. You can get one from Numverify.
     *
     * @see https://numverify.com/product
     *
     * Note: If you are on their free plan, $useHttps = true will not work for you.
     *
     * @param string               $accessKey API access key.
     * @param bool                 $useHttps  (optional) Flag to determine if API calls should use http or https.
     * @param null|ClientInterface $client    (optional) Parameter to provide your own Guzzle client.
     * @param array<string, mixed> $options   (optional) Array of options to pass to the Guzzle client.
     */
    public function __construct(
        #[SensitiveParameter]
        private readonly string $accessKey,
        bool $useHttps = false,
        ?ClientInterface $client = null,
        array $options = []
    );
```

#### Construct API to use HTTPS for API Calls

Note: The Numverify api has different plan options when signing up for an access key. The 'free' plan cannot use the secure (HTTPS) url for the API.

```php
use Numverify\Api;

$useHttps = true;
$api      = new Api($accessKey, $useHttps);  // Optional second parameter
```

#### Construct API to use a Custom Guzzle Client or Options

Note: If creating and passing your own client to `Api`, it will completely ignore `$useHttps`.

```php
use GuzzleHttp\Client;
use Numverify\Api;

$client = new Client([
    'base_uri' => 'http://apilayer.net/api',
    'timeout' => 10
]);

$api = new Api($accessKey, false, $client);
```

If you simply want to change some of Guzzle's default options, pass them along to the optional $options parameter instead.

```php
use Numverify\Api;

$api = new Api($accessKey, false, null, ['timeout' => 10]);

// or
$api = new Api($accessKey, false, options: ['timeout' => 10]);

```

#### Construct API to use a Cache

The Api constructor allows you to pass an optional `$options` parameter, typically used to pass Guzzle options on to the client.

If you specify `cachePath` within `$options`, and it is a valid directory, the constructor will add the cache handler to Guzzle's handler stack.

```php
use Numverify\Api;

$api = new Api($accessKey, false, options: ['cachePath' => '/tmp']);
```

### Exceptions

API failures throw a ```NumverifyApiFailureException```

```php
// Numverify API server error.
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

### Credits

- Author: [Eric Sizemore](https://github.com/ericsizemore)
- Thanks to [all Contributors](https://github.com/ericsizemore/numverify-api-client-php/contributors).
- Special thanks to [JetBrains](https://www.jetbrains.com/?from=esi-numverify-api-client-php) for their Licenses for Open Source Development.

`numverify-api-client-php` is forked from [`markrogoyski/numverify-api-client-php`](https://github.com/markrogoyski/numverify-api-client-php) by [`Mark Rogoyski`](https://github.com/markrogoyski).

My thanks to them, and all their contributors. To view changes in this library in comparison to the original library, please see the [CHANGELOG.md](./CHANGELOG.md) file.

## Contributing

See [CONTRIBUTING](./CONTRIBUTING.md) for more information.

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/numverify-api-client-php/issues).

### Contributor Covenant Code of Conduct

See [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)

### Backward Compatibility Promise

See [backward-compatibility.md](./backward-compatibility.md) for more information on Backwards Compatibility.

### Changelog

See the [CHANGELOG](./CHANGELOG.md) for more information on what has changed recently.

### License

See the [LICENSE](./LICENSE.md) for more information on the license that applies to this project.

### Security

See [SECURITY](./SECURITY.md) for more information on the security disclosure process.
