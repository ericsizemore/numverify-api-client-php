<?php

declare(strict_types=1);

/**
 * This file is part of the Numverify API Client for PHP.
 *
 * (c) 2024 Eric Sizemore <admin@secondversion.com>
 * (c) 2018-2021 Mark Rogoyski <mark@rogoyski.com>
 *
 * This source file is subject to the MIT license. For the full copyright,
 * license information, and credits/acknowledgements, please view the LICENSE
 * and README files that were distributed with this source code.
 */

namespace Numverify\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Iterator;
use Numverify\Api;
use Numverify\Exception\NumverifyApiFailureException;
use Numverify\Exception\NumverifyApiResponseException;
use Numverify\PhoneNumber\Factory;
use Numverify\PhoneNumber\InvalidPhoneNumber;
use Numverify\PhoneNumber\ValidPhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(InvalidPhoneNumber::class)]
#[CoversClass(ValidPhoneNumber::class)]
#[CoversClass(Api::class)]
#[CoversClass(Factory::class)]
#[CoversClass(NumverifyApiFailureException::class)]
class ApiPhoneNumberTest extends TestCase
{
    private const ACCESS_KEY = 'SomeAccessKey';

    /**
     * Data to be used in MockResponse.
     *
     * @var string[]
     */
    private const RESPONSES = [
        'valid'        => '{"valid": true, "number": "14158586273", "local_format": "4158586273", "international_format": "+14158586273", "country_prefix": "+1", "country_code": "US", "country_name": "United States of America", "location": "Novato", "carrier": "AT&T Mobility LLC", "line_type": "mobile"}',
        'invalid'      => '{"valid":false, "number":"183155511", "local_format":"", "international_format":"", "country_prefix":"", "country_code":"", "country_name":"", "location":"", "carrier":"", "line_type":null}',
        'error'        => '{"success":false, "error":{"code":101, "type":"invalid_access_key", "info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"}}',
        'missingField' => '{"valid": true, "number": "14158586273", "local_format": "4158586273", "international_format": "+14158586273", "country_prefix": "+1", "country_code": "US", "country_name": "United States of America", "location": "Novato", "line_type": "mobile"}',
    ];

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a response with missing \'carrier\' field, validatePhoneNumber returns appropriate exception with $useHttps.')]
    public function testValidatePhoneNumberApiResponseMissingData(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(200, body: self::RESPONSES['missingField']),
        ]);
        $apiStub = $this->aClient(self::ACCESS_KEY, $useHttps, mockHandler: $mockHandler);

        $this->expectException(NumverifyApiResponseException::class);
        $this->expectExceptionMessage('API response does not contain one or more expected fields: carrier');
        $phoneNumberToValidate = '14158586273';
        $apiStub->validatePhoneNumber($phoneNumberToValidate);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a non-200 response, validatePhoneNumber returns appropriate exception with $useHttps.')]
    public function testValidatePhoneNumberBadResponse(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(202, body: ''),
        ]);
        $apiStub = $this->aClient(self::ACCESS_KEY, $useHttps, mockHandler: $mockHandler);

        $phoneNumberToValidate = '18314262511';
        $this->expectException(NumverifyApiFailureException::class);
        $apiStub->validatePhoneNumber($phoneNumberToValidate);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given an invalid api access key, validatePhoneNumber returns expected error information with $useHttps.')]
    public function testValidatePhoneNumberInvalidAccessKey(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(200, body: self::RESPONSES['error']),
        ]);
        $apiStub = $this->aClient('InvalidAccessKey', $useHttps, mockHandler: $mockHandler);

        $this->expectException(NumverifyApiFailureException::class);
        $this->expectExceptionMessage('Type:invalid_access_key Code:101 Info:You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]');
        $phoneNumberToValidate = '18314262511';
        $apiStub->validatePhoneNumber($phoneNumberToValidate);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a response with an invalid result, validatePhoneNumber returns expected InvalidPhoneNumber instance with $useHttps.')]
    public function testValidatePhoneNumberInvalidPhoneNumber(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(200, body: self::RESPONSES['invalid']),
        ]);
        $apiStub = $this->aClient(self::ACCESS_KEY, $useHttps, mockHandler: $mockHandler);

        $phoneNumberToValidate = '18314262511';
        $phoneNumber           = $apiStub->validatePhoneNumber($phoneNumberToValidate);
        self::assertInstanceOf(InvalidPhoneNumber::class, $phoneNumber);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a 500 server error response, validatePhoneNumber returns appropriate exception with $useHttps.')]
    public function testValidatePhoneNumberServerError(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(500, body: ''),
        ]);
        $apiStub = $this->aClient(self::ACCESS_KEY, $useHttps, mockHandler: $mockHandler);

        $phoneNumberToValidate = '18314262511';
        $this->expectException(NumverifyApiFailureException::class);
        $apiStub->validatePhoneNumber($phoneNumberToValidate);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a response with a valid result, validatePhoneNumber returns expected ValidPhoneNumber instance with $useHttps.')]
    public function testValidatePhoneNumberValidPhoneNumber(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(200, body: self::RESPONSES['valid']),
        ]);
        $apiStub = $this->aClient(self::ACCESS_KEY, $useHttps, mockHandler: $mockHandler);

        $phoneNumberToValidate = '14158586273';
        $phoneNumber           = $apiStub->validatePhoneNumber($phoneNumberToValidate);
        self::assertInstanceOf(ValidPhoneNumber::class, $phoneNumber);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a response with a valid result, and using country code, validatePhoneNumber returns expected ValidPhoneNumber instance with $useHttps.')]
    public function testValidatePhoneNumberValidPhoneNumberUsingLocalFormatAndCountryCode(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(200, body: self::RESPONSES['valid']),
        ]);
        $apiStub = $this->aClient(self::ACCESS_KEY, $useHttps, mockHandler: $mockHandler);

        $phoneNumberToValidate = '4158586273';
        $countryCode           = 'US';
        $phoneNumber           = $apiStub->validatePhoneNumber($phoneNumberToValidate, $countryCode);
        self::assertInstanceOf(ValidPhoneNumber::class, $phoneNumber);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function dataProviderForHttp(): Iterator
    {
        yield [true];
        yield [false];
    }

    /**
     * Creates a mock client for testing.
     */
    private function aClient(
        string $accessKey = self::ACCESS_KEY,
        bool $useHttps = false,
        ?ClientInterface $client = null,
        ?MockHandler $mockHandler = null
    ): Api&MockObject {
        // Create a mock
        $handlerStack = HandlerStack::create($mockHandler);

        return $this
            ->getMockBuilder(Api::class)
            ->setConstructorArgs([$accessKey, $useHttps, $client, ['handler' => $handlerStack]])
            ->onlyMethods([])
            ->getMock();
    }
}
