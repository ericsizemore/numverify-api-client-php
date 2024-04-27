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

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Iterator;
use Numverify\Api;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_shift;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
#[CoversClass(Api::class)]
class ApiTest extends TestCase
{
    /**
     * Current version of the Numverify package.
     *
     * @var string
     */
    public const LIBRARY_VERSION = '3.0.1';

    private const ACCESS_KEY = 'SomeAccessKey';

    #[TestDox('Verifies the Api::LIBRARY_VERSION constant returns the correct version string.')]
    public function testApiReturnsCurrentLibraryVersion(): void
    {
        self::assertEquals(self::LIBRARY_VERSION, Api::LIBRARY_VERSION);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Construction with default Guzzle client, with cache path and $useHttps.')]
    public function testConstructionWithCachePathOption(bool $useHttps): void
    {
        $api = new Api(self::ACCESS_KEY, $useHttps, null, ['cachePath' => sys_get_temp_dir()]);
        self::assertObjectHasProperty('client', $api); // @phpstan-ignore-line
        self::assertFileExists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'numverify');

        $reflectionClass    = new ReflectionClass($api);
        $reflectionProperty = $reflectionClass->getProperty('client');
        self::assertInstanceOf(ClientInterface::class, $reflectionProperty->getValue($api));
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Construction with custom Guzzle client with $useHttps.')]
    public function testConstructionWithCustomClient(bool $useHttps): void
    {
        $api = new Api(self::ACCESS_KEY, $useHttps, new Client(['base_uri' => 'http://apilayer.net/api']));
        self::assertObjectHasProperty('client', $api); // @phpstan-ignore-line

        $reflectionClass    = new ReflectionClass($api);
        $reflectionProperty = $reflectionClass->getProperty('client');
        self::assertInstanceOf(ClientInterface::class, $reflectionProperty->getValue($api));

        /** @var Client $client */
        $client = $reflectionProperty->getValue($api);

        $client = self::parseGuzzleConfig($client);

        $expected = 'http://apilayer.net/api';
        $actual   = $client['base_uri'];
        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Construction with default Guzzle client with $useHttps.')]
    public function testConstructionWithDefaultClient(bool $useHttps): void
    {
        $api = new Api(self::ACCESS_KEY, $useHttps);
        self::assertObjectHasProperty('client', $api); // @phpstan-ignore-line

        $reflectionClass    = new ReflectionClass($api);
        $reflectionProperty = $reflectionClass->getProperty('client');
        self::assertInstanceOf(ClientInterface::class, $reflectionProperty->getValue($api));

        /** @var Client $client */
        $client = $reflectionProperty->getValue($api);

        $client = self::parseGuzzleConfig($client);

        $expected = ($useHttps ? 'https' : 'http') . '://apilayer.net/api';
        $actual   = $client['base_uri'];
        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderForHttp')]
    #[TestDox('Construction with default Guzzle client, extra options with $useHttps.')]
    public function testConstructionWithDefaultClientExtraOptions(bool $useHttps): void
    {
        $api = new Api(self::ACCESS_KEY, $useHttps, null, ['timeout' => 10]);
        self::assertObjectHasProperty('client', $api); // @phpstan-ignore-line

        $reflectionClass    = new ReflectionClass($api);
        $reflectionProperty = $reflectionClass->getProperty('client');
        self::assertInstanceOf(ClientInterface::class, $reflectionProperty->getValue($api));

        /** @var Client $client */
        $client = $reflectionProperty->getValue($api);

        $client = self::parseGuzzleConfig($client);

        $expected = ($useHttps ? 'https' : 'http') . '://apilayer.net/api';
        $actual   = $client['base_uri'];
        self::assertSame($expected, $actual);
        self::assertSame(10, $client['timeout']);
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
     * @return array<string, int|string>
     */
    private static function parseGuzzleConfig(Client $client): array
    {
        $client = (array) $client;

        /** @var array<array-key, mixed> $config */
        $config = array_shift($client);

        /** @var array<string, int|string> $config */
        $config = [
            'base_uri' => (string) $config['base_uri'], // @phpstan-ignore-line
            'timeout'  => $config['timeout'] ?? 0,
        ];
        return $config;
    }
}
