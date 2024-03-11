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

namespace Numverify\Tests\PhoneNumber;

use Iterator;
use Numverify\{
    Exception\NumverifyApiResponseException,
    PhoneNumber\ValidPhoneNumber
};
use PHPUnit\Framework\{
    Attributes\DataProvider,
    Attributes\CoversClass,
    TestCase
};
use stdClass;

use function json_decode;
use function json_encode;
use function print_r;

/**
 * @internal
 */
#[CoversClass(ValidPhoneNumber::class)]
class ValidPhoneNumberTest extends TestCase
{
    private const VALID = true;

    private const NUMBER = '14158586273';

    private const LOCAL_FORMAT = '4158586273';

    private const INTERNATIONAL_FORMAT = '+14158586273';

    private const COUNTRY_PREFIX = '+1';

    private const COUNTRY_CODE = 'US';

    private const COUNTRY_NAME = 'United States of America';

    private const LOCATION = 'Novato';

    private const CARRIER = 'AT&T Mobility LLC';

    private const LINE_TYPE = 'mobile';

    private stdClass $validatedPhoneNumberData;

    protected function setUp(): void
    {
        $this->validatedPhoneNumberData = new stdClass();
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

    /**
     * @testCase isValid
     */
    public function testIsValid(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        $isValid = $validPhoneNumber->isValid();
        self::assertTrue($isValid);
    }

    /**
     * @testCase getters.
     */
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

    /**
     * @testCase String representation.
     */
    public function testToString(): void
    {
        $validPhoneNumber = new ValidPhoneNumber($this->validatedPhoneNumberData);

        $stringRepresentation = (string) $validPhoneNumber;
        self::assertSame(self::NUMBER, $stringRepresentation);
    }

    /**
     * @testCase JsonSerializable interface.
     */
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

    /**
     * @testCase Debug info.
     */
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

    /**
     * @testCase Missing constructor data exception.
     */
    #[DataProvider('dataProviderForFields')]
    public function testPhoneNumberDataValidation(string $missingField): void
    {
        unset($this->validatedPhoneNumberData->$missingField);

        $this->expectException(NumverifyApiResponseException::class);
        new ValidPhoneNumber($this->validatedPhoneNumberData);
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
