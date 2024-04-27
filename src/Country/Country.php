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

namespace Numverify\Country;

use JsonSerializable;
use Stringable;

use function sprintf;

/**
 * Country
 * Role: Value object that represents a callable country.
 *
 * @see \Numverify\Tests\Country\CountryTest
 */
readonly class Country implements JsonSerializable, Stringable
{
    /**
     * Country constructor.
     */
    public function __construct(private string $countryCode, private string $countryName, private string $dialingCode) {}

    /**
     * {@inheritDoc}
     *
     * CountryCode: CountryName (DialingCode)
     */
    public function __toString(): string
    {
        return sprintf('%s: %s (%s)', $this->countryCode, $this->countryName, $this->dialingCode);
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
     * Get dialing code.
     */
    public function getDialingCode(): string
    {
        return $this->dialingCode;
    }

    /**
     * @inheritDoc
     *
     * @return string[]
     */
    public function jsonSerialize(): array
    {
        return [
            'countryCode'  => $this->countryCode,
            'countryName'  => $this->countryName,
            'diallingCode' => $this->dialingCode,
        ];
    }
}
