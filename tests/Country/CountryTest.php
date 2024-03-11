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

namespace Numverify\Tests\Country;

use PHPUnit\Framework\TestCase;
use Numverify\Country\Country;
use stdClass;

/**
 * @internal
 */
class CountryTest extends TestCase
{
    private const COUNTRY_CODE = 'US';

    private const COUNTRY_NAME = 'United States';

    private const DIALLING_CODE = '+1';

    /**
     * @testCase getters.
     */
    public function testGetters(): void
    {
        $country = new Country(self::COUNTRY_CODE, self::COUNTRY_NAME, self::DIALLING_CODE);

        $countryCode  = $country->getCountryCode();
        $countryName  = $country->getCountryName();
        $diallingCode = $country->getDialingCode();

        self::assertSame(self::COUNTRY_CODE, $countryCode);
        self::assertSame(self::COUNTRY_NAME, $countryName);
        self::assertSame(self::DIALLING_CODE, $diallingCode);
    }

    /**
     * @testCase String representation.
     */
    public function testStringRepresentation(): void
    {
        $country = new Country(self::COUNTRY_CODE, self::COUNTRY_NAME, self::DIALLING_CODE);

        $stringRepresentation = (string) $country;
        self::assertSame('US: United States (+1)', $stringRepresentation);
    }

    /**
     * @testCase JsonSerialize interface.
     */
    public function testJsonSerializeInterface(): void
    {
        $country = new Country(self::COUNTRY_CODE, self::COUNTRY_NAME, self::DIALLING_CODE);

        /** @var non-empty-string $json */
        $json = json_encode($country);

        /** @var stdClass $object */
        $object = json_decode($json);
        self::assertSame(self::COUNTRY_CODE, $object->countryCode);
        self::assertSame(self::COUNTRY_NAME, $object->countryName);
        self::assertSame(self::DIALLING_CODE, $object->diallingCode);
    }
}
