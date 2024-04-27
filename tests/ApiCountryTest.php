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
use Numverify\Country\Collection;
use Numverify\Country\Country;
use Numverify\Exception\NumverifyApiFailureException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Collection::class)]
#[CoversClass(Country::class)]
#[CoversClass(Api::class)]
#[CoversClass(NumverifyApiFailureException::class)]
class ApiCountryTest extends TestCase
{
    private const ACCESS_KEY = 'SomeAccessKey';

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('getCountries returns the correct count, with $useHttps.')]
    public function testCountriesApiReturnsNumberOfCountries(bool $useHttps): void
    {
        $countryCollection = $this->aClient(useHttps: $useHttps)->getCountries();
        self::assertCount(3, $countryCollection);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a non-200 response, getCountries returns appropriate exception with $useHttps.')]
    public function testCountriesBadResponse(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(202),
        ]);
        $stub = $this->aClient(self::ACCESS_KEY, $useHttps, null, $mockHandler);

        $this->expectException(NumverifyApiFailureException::class);
        $stub->getCountries();
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given an invalid api access key, getCountries returns expected error information with $useHttps.')]
    public function testCountriesInvalidAccessKey(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(200, body: '{
                "success":false,
                "error":{
                    "code":101,
                    "type":"invalid_access_key",
                    "info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"
                }
            }', reason: 'Type:invalid_access_key Code:101 Info:You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]'),
        ]);

        $stub = $this->aClient('InvalidAccessKey', $useHttps, null, $mockHandler);

        $this->expectException(NumverifyApiFailureException::class);
        $this->expectExceptionMessage('Type:invalid_access_key Code:101 Info:You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]');
        $stub->getCountries();
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('getCountries returns a Collection containing Country instances, with $useHttps.')]
    public function testCountriesReturnsCollectionOfCountries(bool $useHttps): void
    {
        $countryCollection = $this->aClient(useHttps: $useHttps)->getCountries();
        self::assertInstanceOf(Collection::class, $countryCollection); // @phpstan-ignore-line
        self::assertContainsOnlyInstancesOf(Country::class, $countryCollection);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('getCountries returns expected country information with $useHttps.')]
    public function testCountriesReturnsExpectedCountries(bool $useHttps): void
    {
        $expectedCountries = ['JP' => false, 'GB' => false, 'US' => false];
        $countryCollection = $this->aClient(useHttps: $useHttps)->getCountries();

        foreach ($countryCollection as $countryCode => $country) {
            /** @var string $countryCode */
            $expectedCountries[$countryCode] = true;
            self::assertInstanceOf(Country::class, $country); // @phpstan-ignore-line
        }

        foreach ($expectedCountries as $expectedCountry) {
            self::assertTrue($expectedCountry);
        }
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Given a 500 server error response, getCountries returns appropriate exception with $useHttps.')]
    public function testCountriesServerError(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(500),
        ]);
        $stub = $this->aClient(self::ACCESS_KEY, $useHttps, null, $mockHandler);

        $this->expectException(NumverifyApiFailureException::class);
        $stub->getCountries();
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
        $mockHandler ??= new MockHandler([
            new Response(200, body: '{
                "JP":{"country_name":"Japan","dialling_code":"+81"},
                "GB":{"country_name":"United Kingdom","dialling_code":"+44"},
                "US":{"country_name":"United States","dialling_code":"+1"}
            }'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);

        return $this
            ->getMockBuilder(Api::class)
            ->setConstructorArgs([$accessKey, $useHttps, $client, ['handler' => $handlerStack]])
            ->onlyMethods([])
            ->getMock();
    }
}
