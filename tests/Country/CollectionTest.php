<?php

declare(strict_types=1);

/**
 * This file is part of the Numverify API Client for PHP.
 *
 * (c) 2024 Eric Sizemore <admin@secondversion.com>
 * (c) 2018-2021 Mark Rogoyski
 *
 * @license The MIT License
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Numverify\Tests\Country;

use Numverify\Country\{
    Country,
    Collection
};
use PHPUnit\Framework\{
    Attributes\CoversClass,
    Attributes\DataProvider,
    Attributes\UsesClass,
    TestCase
};
use LogicException;
use Iterator;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Collection::class)]
#[UsesClass(Country::class)]
class CollectionTest extends TestCase
{
    private Country $countryUs;

    private Country $countryGb;

    private Country $countryJp;

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
     * @testCase findByCountryCode
     */
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

    /**
     * @testCase findByCountryName
     */
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
     * @testCase Countable interface.
     *
     * @param Country[] $countries
     */
    #[DataProvider('dataProviderForCountryCounts')]
    public function testCount(array $countries, int $expectedCount): void
    {
        $collection = new Collection(...$countries);
        self::assertCount($expectedCount, $collection);
    }

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

    /**
     * @testCase JsonSerialize interface.
     */
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
        self::assertSame('US', $object->US->countryCode);
        self::assertSame('GB', $object->GB->countryCode);
        self::assertSame('JP', $object->JP->countryCode);
        self::assertSame('United States', $object->US->countryName);
        self::assertSame('United Kingdom', $object->GB->countryName);
        self::assertSame('Japan', $object->JP->countryName);
        self::assertSame('+1', $object->US->diallingCode);
        self::assertSame('+44', $object->GB->diallingCode);
        self::assertSame('+81', $object->JP->diallingCode);
    }

    /**
     * @testCase Iterator interface.
     */
    public function testIterator(): void
    {
        $collection        = new Collection(...[$this->countryUs, $this->countryGb, $this->countryJp]);
        $expectedCountries = ['US' => false, 'GB' => false, 'JP' => false];

        foreach ($collection as $countryCode => $country) {
            $expectedCountries[$countryCode] = true;
            self::assertInstanceOf(Country::class, $country); // @phpstan-ignore-line
        }

        foreach ($expectedCountries as $expectedCountry) {
            self::assertTrue($expectedCountry);
        }
    }

    /**
     * @testCase Iteration failure if manually manipulating the iterator (with elements).
     */
    public function testIterationCurrentFailureWithElements(): void
    {
        $collection = new Collection(...[$this->countryUs]);
        $collection->next();

        $this->expectException(LogicException::class);
        $collection->current();
    }

    /**
     * @testCase Iteration failure if manually manipulating the iterator (no elements).
     */
    public function testIterationCurrentFailureNoElements(): void
    {
        $collection = new Collection(...[]);

        $this->expectException(LogicException::class);
        $collection->current();
    }
}
