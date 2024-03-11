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
    Country\Collection,
    Country\Country,
    Exception\NumverifyApiFailureException
};

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

    /**
     * @testCase getCountries success.
     */
    #[DataProvider('dataProviderForHttp')]
    public function testCountriesApiReturnsNumberOfCountries(bool $useHttps): void
    {
        $countryCollection = $this->aClient(useHttps: $useHttps)->getCountries();
        self::assertCount(3, $countryCollection);
    }

    /**
     * @testCase     getCountries success
     */
    #[DataProvider('dataProviderForHttp')]
    public function testCountriesReturnsCollectionOfCountries(bool $useHttps): void
    {
        $countryCollection = $this->aClient(useHttps: $useHttps)->getCountries();
        self::assertInstanceOf(Collection::class, $countryCollection); // @phpstan-ignore-line
        self::assertContainsOnlyInstancesOf(Country::class, $countryCollection);
    }

    /**
     * @testCase getCountries success.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase getCountries exception - invalid access key.
     */
    #[DataProvider('dataProviderForHttp')]
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

    /**
     * @testCase getCountries exception - API server error.
     */
    #[DataProvider('dataProviderForHttp')]
    public function testValidatePhoneNumberServerError(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(500),
        ]);
        $stub = $this->aClient(self::ACCESS_KEY, $useHttps, null, $mockHandler);

        $this->expectException(NumverifyApiFailureException::class);
        $stub->getCountries();
    }

    /**
     * @testCase     getCountries exception - API bad response
     */
    #[DataProvider('dataProviderForHttp')]
    public function testValidatePhoneNumberBadResponse(bool $useHttps): void
    {
        $mockHandler = new MockHandler([
            new Response(202),
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
     * Given a client.
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
