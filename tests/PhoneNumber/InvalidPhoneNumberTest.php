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
    PhoneNumber\InvalidPhoneNumber
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
#[CoversClass(InvalidPhoneNumber::class)]
class InvalidPhoneNumberTest extends TestCase
{
    private const VALID = false;

    private const NUMBER = '14158586273';

    private stdClass $validatedPhoneNumberData;

    protected function setUp(): void
    {
        $this->validatedPhoneNumberData = new stdClass();
        $this->validatedPhoneNumberData->valid = self::VALID;
        $this->validatedPhoneNumberData->number = self::NUMBER;
    }

    /**
     * @testCase isValid
     */
    public function testIsValid(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $isValid = $invalidPhoneNumber->isValid();
        self::assertFalse($isValid);
    }

    /**
     * @testCase getters
     */
    public function testGetters(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $number = $invalidPhoneNumber->getNumber();
        self::assertSame(self::NUMBER, $number);
    }

    /**
     * @testCase String representation
     */
    public function testToString(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $stringRepresentation = (string) $invalidPhoneNumber;
        self::assertSame(self::NUMBER, $stringRepresentation);
    }

    /**
     * @testCase JsonSerializable interface
     */
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

    /**
     * @testCase Debug info
     */
    public function testDebugInfo(): void
    {
        $invalidPhoneNumber = new InvalidPhoneNumber($this->validatedPhoneNumberData);

        $debugInfo = print_r($invalidPhoneNumber, true);
        self::assertStringContainsString('valid', $debugInfo);
        self::assertStringContainsString('number', $debugInfo);
    }

    /**
     * @testCase     Missing constructor data exception
     */
    #[DataProvider('dataProviderForFields')]
    public function testPhoneNumberDataValidation(string $missingField): void
    {
        unset($this->validatedPhoneNumberData->$missingField);

        $this->expectException(NumverifyApiResponseException::class);
        new InvalidPhoneNumber($this->validatedPhoneNumberData);
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
