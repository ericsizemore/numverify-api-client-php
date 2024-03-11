<?php

namespace Numverify\Country;

use JsonSerializable;
use Stringable;

use function sprintf;

/**
 * Country
 * Role: Value object that represents a callable country
 */
readonly class Country implements Stringable, JsonSerializable
{
    /**
     * Country constructor
     */
    public function __construct(private string $countryCode, private string $countryName, private string $dialingCode) {}

    /**
     * Get country code
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Get country name
     */
    public function getCountryName(): string
    {
        return $this->countryName;
    }

    /**
     * Get dialing code
     */
    public function getDialingCode(): string
    {
        return $this->dialingCode;
    }

    /**
     * {@inheritdoc}
     *
     * CountryCode: CountryName (DialingCode)
     */
    public function __toString(): string
    {
        return sprintf('%s: %s (%s)', $this->countryCode, $this->countryName, $this->dialingCode);
    }

    /**
     * {@inheritdoc}
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
