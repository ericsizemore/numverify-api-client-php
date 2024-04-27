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

use Countable;
use Iterator;
use JsonSerializable;
use LogicException;

use function current;
use function key;
use function next;
use function reset;

/**
 * Country Collection
 * Role: Collection of callable countries.
 *
 * @implements Iterator<Country>
 *
 * @see \Numverify\Tests\Country\CollectionTest
 */
class Collection implements Countable, Iterator, JsonSerializable
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
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->byCountryCode);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     *
     * @return object[]
     */
    public function jsonSerialize(): array
    {
        return $this->byCountryCode;
    }

    /**
     * @inheritDoc
     */
    public function key(): null|int|string
    {
        return key($this->byCountryCode);
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        next($this->byCountryCode);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->byCountryCode);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return key($this->byCountryCode) !== null;
    }
}
