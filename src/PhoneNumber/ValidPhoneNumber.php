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
use function sprintf;

/**
 * ValidPhoneNumber
 * Role: Value object to represent a phone number that the Numverify returned as valid.
 *
 * @see \Numverify\Tests\PhoneNumber\ValidPhoneNumberTest
 */
class ValidPhoneNumber implements PhoneNumberInterface
{
    /**
     * @var string[]
     */
    private const FIELDS = [
        'valid', 'number', 'local_format', 'international_format', 'country_prefix',
        'country_code', 'country_name', 'location', 'carrier', 'line_type',
    ];

    private readonly string $carrier;

    private readonly string $countryCode;

    private readonly string $countryName;

    private readonly string $countryPrefix;

    private readonly string $internationalFormat;

    private readonly string $lineType;

    private readonly string $localFormat;

    private readonly string $location;

    private readonly string $number;
    private readonly bool $valid;

    /**
     * ValidPhoneNumber constructor.
     */
    public function __construct(stdClass $validatedPhoneNumberData)
    {
        $this->verifyPhoneNumberData($validatedPhoneNumberData);

        $this->valid               = (bool) $validatedPhoneNumberData->valid;
        $this->number              = (string) $validatedPhoneNumberData->number;
        $this->localFormat         = (string) $validatedPhoneNumberData->local_format;
        $this->internationalFormat = (string) $validatedPhoneNumberData->international_format;
        $this->countryPrefix       = (string) $validatedPhoneNumberData->country_prefix;
        $this->countryCode         = (string) $validatedPhoneNumberData->country_code;
        $this->countryName         = (string) $validatedPhoneNumberData->country_name;
        $this->location            = (string) $validatedPhoneNumberData->location;
        $this->carrier             = (string) $validatedPhoneNumberData->carrier;
        $this->lineType            = (string) $validatedPhoneNumberData->line_type;
    }

    /**
     * Debug info.
     *
     * @return array{
     *     valid: bool,
     *     number: string,
     *     localFormat: string,
     *     internationalFormat: string,
     *     countryPrefix: string,
     *     countryCode: string,
     *     countryName: string,
     *     location: string,
     *     carrier: string,
     *     lineType: string
     * }
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
     * Get carrier.
     */
    public function getCarrier(): string
    {
        return $this->carrier;
    }

    /**
     * Get country code.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Get country name.
     */
    public function getCountryName(): string
    {
        return $this->countryName;
    }

    /**
     * Get country prefix.
     */
    public function getCountryPrefix(): string
    {
        return $this->countryPrefix;
    }

    /**
     * Get international format.
     */
    public function getInternationalFormat(): string
    {
        return $this->internationalFormat;
    }

    /**
     * Get line type.
     */
    public function getLineType(): string
    {
        return $this->lineType;
    }

    /**
     * Get local format.
     */
    public function getLocalFormat(): string
    {
        return $this->localFormat;
    }

    /**
     * Get location.
     */
    public function getLocation(): string
    {
        return $this->location;
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
        return true;
    }

    /**
     * @inheritDoc
     *
     * @return array{
     *     valid: bool,
     *     number: string,
     *     localFormat: string,
     *     internationalFormat: string,
     *     countryPrefix: string,
     *     countryCode: string,
     *     countryName: string,
     *     location: string,
     *     carrier: string,
     *     lineType: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'valid'               => $this->valid,
            'number'              => $this->number,
            'localFormat'         => $this->localFormat,
            'internationalFormat' => $this->internationalFormat,
            'countryPrefix'       => $this->countryPrefix,
            'countryCode'         => $this->countryCode,
            'countryName'         => $this->countryName,
            'location'            => $this->location,
            'carrier'             => $this->carrier,
            'lineType'            => $this->lineType,
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
            throw new NumverifyApiResponseException(sprintf(
                "API response does not contain one or more expected fields: %s",
                implode(', ', $missingFields)
            ));
        }
    }
}
