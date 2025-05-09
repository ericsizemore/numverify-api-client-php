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

namespace Numverify\Tests\PhoneNumber;

use Iterator;
use Numverify\Exception\NumverifyApiResponseException;
use Numverify\PhoneNumber\ValidPhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use stdClass;

use function json_decode;
use function json_encode;
use function print_r;

/**
 * @internal
 */
#[CoversClass(ValidPhoneNumber::class)]
final class ValidPhoneNumberTest extends TestCase
{
    private const CARRIER = 'AT&T Mobility LLC';

    private const COUNTRY_CODE = 'US';

    private const COUNTRY_NAME = 'United States of America';

    private const COUNTRY_PREFIX = '+1';

    private const INTERNATIONAL_FORMAT = '+14158586273';

    private const LINE_TYPE = 'mobile';

    private const LOCAL_FORMAT = '4158586273';

    private const LOCATION = 'Novato';

    private const NUMBER = '14158586273';

    private const VALID = true;

    private stdClass $validatedPhoneNumberData;

    protected function setUp(): void
    {
        $this->validatedPhoneNumberData                       = new stdClass();
        $this->validatedPhoneNumberData->valid                = self::VALID;
        $this->validatedPhoneNumberData->number               = self::NUMBER;
        $this->validatedPhoneNumberData->local_format         = self::LOCAL_FORMAT;
        $this->validatedPhoneNumberData->international_format = self::INTERNATIONAL_FORMAT;
        $this->validatedPhoneNumberData->country_prefix       = self::COUNTRY_PREFIX;
        $this->validatedPhoneNumberData->country_code         = self::COUNTRY_CODE;
        $this->validatedPhoneNumberData->country_name         = self::COUNTRY_NAME;
        $this->validatedPhoneNumberData->location             = self::LOCATION;
        $this->validatedPhoneNumberData->carrier              = self::CARRIER;
        $this->validatedPhoneNumberData->line_type            = self::LINE_TYPE;
    }

    #[TestDox('ValidPhoneNumber sets __debugInfo for var_dump to return number data as array.')]
    public function testDebugInfo(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        $debugInfo = print_r($validPhoneNumber, true);

        self::assertStringContainsString('valid', $debugInfo);
        self::assertStringContainsString('number', $debugInfo);
        self::assertStringContainsString('localFormat', $debugInfo);
        self::assertStringContainsString('internationalFormat', $debugInfo);
        self::assertStringContainsString('countryPrefix', $debugInfo);
        self::assertStringContainsString('countryCode', $debugInfo);
        self::assertStringContainsString('countryName', $debugInfo);
        self::assertStringContainsString('location', $debugInfo);
        self::assertStringContainsString('carrier', $debugInfo);
        self::assertStringContainsString('lineType', $debugInfo);
    }

    #[TestDox("ValidPhoneNumber 'getters' returns appropriate data.")]
    public function testGetters(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        $number              = $validPhoneNumber->getNumber();
        $localFormat         = $validPhoneNumber->getLocalFormat();
        $internationalFormat = $validPhoneNumber->getInternationalFormat();
        $countryPrefix       = $validPhoneNumber->getCountryPrefix();
        $countryCode         = $validPhoneNumber->getCountryCode();
        $countryName         = $validPhoneNumber->getCountryName();
        $location            = $validPhoneNumber->getLocation();
        $carrier             = $validPhoneNumber->getCarrier();
        $lineType            = $validPhoneNumber->getLineType();

        self::assertSame(self::NUMBER, $number);
        self::assertSame(self::LOCAL_FORMAT, $localFormat);
        self::assertSame(self::INTERNATIONAL_FORMAT, $internationalFormat);
        self::assertSame(self::COUNTRY_PREFIX, $countryPrefix);
        self::assertSame(self::COUNTRY_CODE, $countryCode);
        self::assertSame(self::COUNTRY_NAME, $countryName);
        self::assertSame(self::LOCATION, $location);
        self::assertSame(self::CARRIER, $carrier);
        self::assertSame(self::LINE_TYPE, $lineType);
    }

    #[TestDox('ValidPhoneNumber returns true on isValid.')]
    public function testIsValid(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        $isValid = $validPhoneNumber->isValid();
        self::assertTrue($isValid);
    }

    #[TestDox('ValidPhoneNumber uses JsonSerializable interface to return number data as array.')]
    public function testJsonSerialize(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        /** @var non-empty-string $json */
        $json = json_encode($validPhoneNumber);

        /** @var stdClass $object */
        $object = json_decode($json);
        self::assertSame(self::VALID, $object->valid);
        self::assertSame(self::NUMBER, $object->number);
        self::assertSame(self::LOCAL_FORMAT, $object->localFormat);
        self::assertSame(self::INTERNATIONAL_FORMAT, $object->internationalFormat);
        self::assertSame(self::COUNTRY_PREFIX, $object->countryPrefix);
        self::assertSame(self::COUNTRY_CODE, $object->countryCode);
        self::assertSame(self::COUNTRY_NAME, $object->countryName);
        self::assertSame(self::LOCATION, $object->location);
        self::assertSame(self::CARRIER, $object->carrier);
        self::assertSame(self::LINE_TYPE, $object->lineType);
    }

    #[DataProvider('dataProviderForFields')]
    #[TestDox('ValidPhoneNumber throws a NumverifyApiResponseException exception if missing data. Using field: $missingField')]
    public function testPhoneNumberDataValidation(string $missingField): void
    {
        unset($this->validatedPhoneNumberData->$missingField);

        $this->expectException(NumverifyApiResponseException::class);
        new ValidPhoneNumber($this->validatedPhoneNumberData);
    }

    #[TestDox('ValidPhoneNumber uses Stringable interface to return proper string representation of number data.')]
    public function testToString(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        $stringRepresentation = (string) $validPhoneNumber;
        self::assertSame(self::NUMBER, $stringRepresentation);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function dataProviderForFields(): Iterator
    {
        yield ['valid'];
        yield ['number'];
        yield ['local_format'];
        yield ['international_format'];
        yield ['country_prefix'];
        yield ['country_code'];
        yield ['country_name'];
        yield ['location'];
        yield ['carrier'];
        yield ['line_type'];
    }
}
