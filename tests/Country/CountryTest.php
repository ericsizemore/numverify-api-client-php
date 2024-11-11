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

namespace Numverify\Tests\Country;

use Numverify\Country\Country;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Country::class)]
class CountryTest extends TestCase
{
    private const COUNTRY_CODE = 'US';

    private const COUNTRY_NAME = 'United States';

    private const DIALLING_CODE = '+1';

    #[TestDox('Country \'getters\' returns expected results.')]
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

    #[TestDox('Country implements JsonSerialize and returns array of country information.')]
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

    #[TestDox('Country implements Stringable and returns correct string representation.')]
    public function testStringRepresentation(): void
    {
        $country = new Country(self::COUNTRY_CODE, self::COUNTRY_NAME, self::DIALLING_CODE);

        $stringRepresentation = (string) $country;
        self::assertSame('US: United States (+1)', $stringRepresentation);
    }
}
