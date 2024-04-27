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
use Numverify\PhoneNumber\InvalidPhoneNumber;
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
#[CoversClass(InvalidPhoneNumber::class)]
class InvalidPhoneNumberTest extends TestCase
{
    private const NUMBER = '14158586273';
    private const VALID  = false;

    private stdClass $validatedPhoneNumberData;

    protected function setUp(): void
    {
        $this->validatedPhoneNumberData         = new stdClass();
        $this->validatedPhoneNumberData->valid  = self::VALID;
        $this->validatedPhoneNumberData->number = self::NUMBER;
    }

    #[TestDox('InvalidPhoneNumber sets __debugInfo for var_dump to return number data as array.')]
    public function testDebugInfo(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $debugInfo = print_r($invalidPhoneNumber, true);
        self::assertStringContainsString('valid', $debugInfo);
        self::assertStringContainsString('number', $debugInfo);
    }

    #[TestDox('InvalidPhoneNumber getNumber returns number.')]
    public function testGetters(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $number = $invalidPhoneNumber->getNumber();
        self::assertSame(self::NUMBER, $number);
    }

    #[TestDox('InvalidPhoneNumber returns false on isValid.')]
    public function testIsValid(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $isValid = $invalidPhoneNumber->isValid();
        self::assertFalse($isValid);
    }

    #[TestDox('InvalidPhoneNumber uses JsonSerializable interface to return number data as array.')]
    public function testJsonSerialize(): void
    {
        // Given
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        /** @var non-empty-string $json */
        $json = json_encode($invalidPhoneNumber);

        /** @var stdClass $object */
        $object = json_decode($json);
        self::assertSame(self::VALID, $object->valid);
        self::assertSame(self::NUMBER, $object->number);
    }

    #[DataProvider('dataProviderForFields')]
    #[TestDox('InvalidPhoneNumber throws a NumverifyApiResponseException exception if missing data. Using field: $missingField')]
    public function testPhoneNumberDataValidation(string $missingField): void
    {
        unset($this->validatedPhoneNumberData->$missingField);

        $this->expectException(NumverifyApiResponseException::class);
        new InvalidPhoneNumber($this->validatedPhoneNumberData);
    }

    #[TestDox('InvalidPhoneNumber uses Stringable interface to return proper string representation.')]
    public function testToString(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $stringRepresentation = (string) $invalidPhoneNumber;
        self::assertSame(self::NUMBER, $stringRepresentation);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function dataProviderForFields(): Iterator
    {
        yield ['valid'];
        yield ['number'];
    }
}
