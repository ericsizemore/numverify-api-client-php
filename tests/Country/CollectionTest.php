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

namespace Numverify\Tests\Country;

use Iterator;
use LogicException;
use Numverify\Country\Collection;
use Numverify\Country\Country;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Collection::class)]
#[UsesClass(Country::class)]
class CollectionTest extends TestCase
{
    private Country $countryGb;

    private Country $countryJp;
    private Country $countryUs;

    /**
     * Set up countries.
     */
    protected function setUp(): void
    {
        $this->countryUs = new Country('US', 'United States', '+1');
        $this->countryGb = new Country('GB', 'United Kingdom', '+44');
        $this->countryJp = new Country('JP', 'Japan', '+81');
    }

    /**
     * @param Country[] $countries
     */
    #[DataProvider('dataProviderForCountryCounts')]
    #[TestDox('Collection uses the Countable interface to implement count. Using $countries results in $expectedCount.')]
    public function testCount(array $countries, int $expectedCount): void
    {
        $collection = new Collection(...$countries);

        self::assertSame($expectedCount, $collection->count());
        self::assertCount($expectedCount, $collection);
    }

    #[TestDox('Collection is able to find countries by country code.')]
    public function testFindByCountryCode(): void
    {
        $collection = new Collection(...[$this->countryUs, $this->countryGb, $this->countryJp]);

        $countryUs = $collection->findByCountryCode($this->countryUs->getCountryCode());
        $countryGb = $collection->findByCountryCode($this->countryGb->getCountryCode());
        $countryJp = $collection->findByCountryCode($this->countryJp->getCountryCode());

        self::assertSame($this->countryUs, $countryUs);
        self::assertSame($this->countryGb, $countryGb);
        self::assertSame($this->countryJp, $countryJp);
    }

    #[TestDox('Collection is able to find countries by country name.')]
    public function testFindByCountryName(): void
    {
        $collection = new Collection(...[$this->countryUs, $this->countryGb, $this->countryJp]);

        $countryUs = $collection->findByCountryName($this->countryUs->getCountryName());
        $countryGb = $collection->findByCountryName($this->countryGb->getCountryName());
        $countryJp = $collection->findByCountryName($this->countryJp->getCountryName());

        self::assertSame($this->countryUs, $countryUs);
        self::assertSame($this->countryGb, $countryGb);
        self::assertSame($this->countryJp, $countryJp);
    }

    /**
     * @psalm-suppress NoValue
     */
    #[TestDox('Collection iteration failure if manually manipulating the iterator (no elements).')]
    public function testIterationCurrentFailureNoElements(): void
    {
        $collection = new Collection(...[]);

        $this->expectException(LogicException::class);
        $collection->current();
    }

    #[TestDox('Collection iteration failure if manually manipulating the iterator (with elements).')]
    public function testIterationCurrentFailureWithElements(): void
    {
        $collection = new Collection(...[$this->countryUs]);
        $collection->next();

        $this->expectException(LogicException::class);
        $collection->current();
    }

    #[TestDox('Collection can be iterated due to implementing the Iterator interface.')]
    public function testIterator(): void
    {
        $collection        = new Collection(...[$this->countryUs, $this->countryGb, $this->countryJp]);
        $expectedCountries = ['US' => false, 'GB' => false, 'JP' => false];

        foreach ($collection as $countryCode => $country) {
            /** @var string $countryCode */
            $expectedCountries[$countryCode] = true;
            self::assertInstanceOf(Country::class, $country); // @phpstan-ignore-line
        }

        foreach ($expectedCountries as $expectedCountry) {
            self::assertTrue($expectedCountry);
        }
    }

    #[TestDox('Collection uses the JsonSerialize interface to return country information as an array.')]
    public function testJsonSerialize(): void
    {
        $collection = new Collection(...[$this->countryUs, $this->countryGb, $this->countryJp]);

        /** @var non-empty-string $json */
        $json = json_encode($collection);

        /** @var stdClass $object */
        $object = json_decode($json);
        self::assertObjectHasProperty('US', $object);
        self::assertObjectHasProperty('GB', $object);
        self::assertObjectHasProperty('JP', $object);

        /** @var stdClass $countryUs */
        $countryUs = $object->US;
        /** @var stdClass $countryGb */
        $countryGb = $object->GB;
        /** @var stdClass $countryJp */
        $countryJp = $object->JP;

        self::assertSame('US', (string) $countryUs->countryCode);
        self::assertSame('GB', (string) $countryGb->countryCode);
        self::assertSame('JP', (string) $countryJp->countryCode);
        self::assertSame('United States', (string) $countryUs->countryName);
        self::assertSame('United Kingdom', (string) $countryGb->countryName);
        self::assertSame('Japan', (string) $countryJp->countryName);
        self::assertSame('+1', (string) $countryUs->diallingCode);
        self::assertSame('+44', (string) $countryGb->diallingCode);
        self::assertSame('+81', (string) $countryJp->diallingCode);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function dataProviderForCountryCounts(): Iterator
    {
        yield 'zero' => [
            [],
            0,
        ];
        yield 'one' => [
            [new Country('US', 'United States', '+1')],
            1,
        ];
        yield 'two' => [
            [
                new Country('US', 'United States', '+1'),
                new Country('GB', 'United Kingdom', '+44'),
            ],
            2,
        ];
        yield 'three' => [
            [
                new Country('US', 'United States', '+1'),
                new Country('GB', 'United Kingdom', '+44'),
                new Country('JP', 'Japan', '+81'),
            ],
            3,
        ];
    }
}
