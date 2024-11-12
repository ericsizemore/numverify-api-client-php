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

namespace Numverify\PhoneNumber;

use Numverify\Exception\NumverifyApiResponseException;
use stdClass;

use function implode;

/**
 * InvalidPhoneNumber
 * Role: Value object to represent a phone number that the Numverify returned as invalid.
 *
 * @see \Numverify\Tests\PhoneNumber\InvalidPhoneNumberTest
 */
class InvalidPhoneNumber implements PhoneNumberInterface
{
    private const FIELDS = ['valid', 'number'];

    private readonly string $number;
    private readonly bool $valid;

    /**
     * InvalidPhoneNumber constructor.
     */
    public function __construct(stdClass $validatedPhoneNumber)
    {
        $this->verifyPhoneNumberData($validatedPhoneNumber);
        \assert(\is_bool($validatedPhoneNumber->valid));
        \assert(\is_string($validatedPhoneNumber->number));

        $this->valid  = $validatedPhoneNumber->valid;
        $this->number = $validatedPhoneNumber->number;
    }

    /**
     * Debug info.
     *
     * @return array<string, bool|string>
     */
    public function __debugInfo(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->number;
    }

    /**
     * @inheritDoc
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     *
     * @return array<string, bool|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'valid'  => $this->valid,
            'number' => $this->number,
        ];
    }

    /**
     * Verify the phone number data contains the expected fields.
     *
     * @throws NumverifyApiResponseException
     */
    private function verifyPhoneNumberData(stdClass $phoneNumberData): void
    {
        $missingFields = [];

        foreach (self::FIELDS as $field) {
            if (!isset($phoneNumberData->$field)) {
                $missingFields[] = $field;
            }
        }

        if ($missingFields !== []) {
            throw new NumverifyApiResponseException(\sprintf(
                "API response does not contain one or more expected fields: %s",
                implode(', ', $missingFields)
            ));
        }
    }
}
