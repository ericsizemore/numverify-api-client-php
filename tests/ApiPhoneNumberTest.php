<?php

declare(strict_types=1);

/**
 * This file is part of the Numverify API Client for PHP.
 *
 * (c) 2024 Eric Sizemore <admin@secondversion.com>
 * (c) 2018-2021 Mark Rogoyski
 *
 * @license The MIT License
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Numverify\Tests;

use GuzzleHttp\{
    ClientInterface,
    Handler\MockHandler,
    HandlerStack,
    Psr7\Response
};
use Iterator;
use PHPUnit\Framework\{
    Attributes\CoversClass,
    Attributes\DataProvider,
    TestCase,
    MockObject\MockObject
};
use Numverify\{
    Api,
    Exception\NumverifyApiFailureException,
    Exception\NumverifyApiResponseException,
    PhoneNumber\Factory,
    PhoneNumber\InvalidPhoneNumber,
    PhoneNumber\ValidPhoneNumber
};

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
     * @var string[]
     */
    private const RESPONSES = [
        'valid'        => '{"valid": true, "number": "14158586273", "local_format": "4158586273", "international_format": "+14158586273", "country_prefix": "+1", "country_code": "US", "country_name": "United States of America", "location": "Novato", "carrier": "AT&T Mobility LLC", "line_type": "mobile"}',
        'invalid'      => '{"valid":false, "number":"183155511", "local_format":"", "international_format":"", "country_prefix":"", "country_code":"", "country_name":"", "location":"", "carrier":"", "line_type":null}',
        'error'        => '{"success":false, "error":{"code":101, "type":"invalid_access_key", "info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"}}',
        'missingField' => '{"valid": true, "number": "14158586273", "local_format": "4158586273", "international_format": "+14158586273", "country_prefix": "+1", "country_code": "US", "country_name": "United States of America", "location": "Novato", "line_type": "mobile"}',
    ];

    /**
     * Given a client.
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

    /**
     * @testCase validatePhoneNumber success - valid phone number.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase validatePhoneNumber success - valid phone number using local format and country code.
     */
    #[DataProvider('dataProviderForHttp')]
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
     * @testCase validatePhoneNumber success - invalid phone number.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase validatePhoneNumber exception - invalid access key.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase validatePhoneNumber exception - Server Error.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase validatePhoneNumber exception - Bad response.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase validatePhoneNumber exception - API response missing expected field "carrier".
     */
    #[DataProvider('dataProviderForHttp')]
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

    public static function dataProviderForHttp(): Iterator
    {
        yield [true];
        yield [false];
    }
}
