<?php

declare(strict_types=1);

/**
 * This file is part of the Numverify API Client for PHP.
 *
 * (c) 2024 Eric Sizemore <admin@secondversion.com>
 * (c) 2018-2021 Mark Rogoyski <mark@rogoyski.com>
 *
 * @license The MIT License
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Numverify\Tests;

use GuzzleHttp\{
    Client,
    ClientInterface
};
use Iterator;
use Numverify\Api;
use PHPUnit\Framework\{
    Attributes\CoversClass,
    Attributes\DataProvider,
    Attributes\TestDox,
    TestCase
};
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
    private const ACCESS_KEY = 'SomeAccessKey';

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

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function dataProviderForHttp(): Iterator
    {
        yield [true];
        yield [false];
    }

    /**
     * @return array<string, string|int>
     */
    private static function parseGuzzleConfig(Client $client): array
    {
        $client = (array) $client;

        /** @var array<array-key, mixed> $config */
        $config = array_shift($client);

        /** @var array<string, string|int> $config */
        $config = [
            'base_uri' => (string) $config['base_uri'], // @phpstan-ignore-line
            'timeout'  => $config['timeout'] ?? 0,
        ];
        return $config;
    }
}
