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

namespace Numverify\PhoneNumber;

use Numverify\Exception\NumverifyApiResponseException;
use stdClass;

use function implode;
use function sprintf;

/**
 * InvalidPhoneNumber
 * Role: Value object to represent a phone number that the Numverify returned as invalid.
 *
 * @see \Numverify\Tests\PhoneNumber\InvalidPhoneNumberTest
 */
class InvalidPhoneNumber implements PhoneNumberInterface
{
    private readonly bool $valid;

    private readonly string $number;

    private const FIELDS = ['valid', 'number'];

    /**
     * InvalidPhoneNumber constructor.
     */
    public function __construct(stdClass $validatedPhoneNumber)
    {
        $this->verifyPhoneNumberData($validatedPhoneNumber);

        $this->valid  = (bool) $validatedPhoneNumber->valid;
        $this->number = (string) $validatedPhoneNumber->number;
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
     */
    public function getNumber(): string
    {
        return $this->number;
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
     * Debug info.
     *
     * @return array<string, bool|string>
     */
    public function __debugInfo(): array
    {
        return $this->jsonSerialize();
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
            throw new NumverifyApiResponseException(sprintf(
                "API response does not contain one or more expected fields: %s",
                implode(', ', $missingFields)
            ));
        }
    }
}
