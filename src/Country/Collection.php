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
 * Role: Collection of callable countries.
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
     * @inheritDoc
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function count(): int
    {
        return count($this->byCountryCode);
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
    public function rewind(): void
    {
        reset($this->byCountryCode);
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
     * @inheritDoc
     */
    public function key(): int | string | null
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
    public function valid(): bool
    {
        return key($this->byCountryCode) !== null;
    }
}
