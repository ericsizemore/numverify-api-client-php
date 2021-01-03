<?php

namespace Numverify\Country;

/**
 * Country Collection
 * Role: Collection of callable countries
 * @implements \Iterator<Country>
 */
class Collection implements \Iterator, \Countable, \JsonSerializable
{
    /** @var Country[] */
    private $countriesByCountryCode = [];

    /** @var Country[] */
    private $countriesByName = [];

    /**
     * Collection constructor
     *
     * @param Country ...$countries
     */
    public function __construct(Country ...$countries)
    {
        foreach ($countries as $country) {
            $this->countriesByCountryCode[$country->getCountryCode()] = $country;
            $this->countriesByName[$country->getCountryName()]        = $country;
        }
    }

    /**
     * Find country by country code
     *
     * @param string $countryCode
     *
     * @return Country|null
     */
    public function findByCountryCode(string $countryCode): ?Country
    {
        return $this->countriesByCountryCode[$countryCode] ?? null;
    }

    /**
     * Find country by name
     *
     * @param string $countryName
     *
     * @return Country|null
     */
    public function findByCountryName(string $countryName): ?Country
    {
        return $this->countriesByName[$countryName] ?? null;
    }

    /**
     * Countable interface
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->countriesByCountryCode);
    }

    /**
     * JsonSerializable interface
     *
     * @return object[]
     */
    public function jsonSerialize(): array
    {
        return $this->countriesByCountryCode;
    }

    /**
     * Iterator interface
     */
    public function rewind(): void
    {
        reset($this->countriesByCountryCode);
    }

    /**
     * Iterator interface
     *
     * @return Country
     */
    public function current(): Country
    {
        $country = current($this->countriesByCountryCode);
        if ($country === false) {
            throw new \LogicException('Iteration error - current returned false');
        }
        return $country;
    }

    /**
     * Iterator interface
     *
     * @return int|string|null
     */
    public function key()
    {
        return key($this->countriesByCountryCode);
    }

    /**
     * Iterator interface
     */
    public function next(): void
    {
        next($this->countriesByCountryCode);
    }

    /**
     * Iterator interface
     *
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->countriesByCountryCode) !== null;
    }
}
