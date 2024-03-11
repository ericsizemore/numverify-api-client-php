<?php

namespace Numverify\Country;

use Countable;
use Iterator;
use JsonSerializable;
use LogicException;

use function count;
use function current;
use function key;
use function next;
use function reset;

/**
 * Country Collection
 * Role: Collection of callable countries
 *
 * @implements Iterator<Country>
 */
class Collection implements Iterator, Countable, JsonSerializable
{
    /** @var Country[] */
    private array $byCountryCode = [];

    /** @var Country[] */
    private array $byName = [];

    /**
     * Collection constructor.
     */
    public function __construct(Country ...$countries)
    {
        foreach ($countries as $country) {
            $this->byCountryCode[$country->getCountryCode()] = $country;
            $this->byName[$country->getCountryName()]        = $country;
        }
    }

    /**
     * Find country by country code.
     */
    public function findByCountryCode(string $countryCode): ?Country
    {
        return $this->byCountryCode[$countryCode] ?? null;
    }

    /**
     * Find country by name.
     */
    public function findByCountryName(string $countryName): ?Country
    {
        return $this->byName[$countryName] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->byCountryCode);
    }

    /**
     * {@inheritdoc}
     *
     * @return object[]
     */
    public function jsonSerialize(): array
    {
        return $this->byCountryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        reset($this->byCountryCode);
    }

    /**
     * {@inheritdoc}
     */
    public function current(): Country
    {
        $country = current($this->byCountryCode);

        if ($country === false) {
            throw new LogicException('Iteration error - current returned false');
        }

        return $country;
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int | string | null
    {
        return key($this->byCountryCode);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        next($this->byCountryCode);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return key($this->byCountryCode) !== null;
    }
}
