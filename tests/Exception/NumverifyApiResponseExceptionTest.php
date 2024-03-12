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

namespace Numverify\Tests\Exception;

use Numverify\Exception\NumverifyApiResponseException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
class NumverifyApiResponseExceptionTest extends TestCase
{
    /**
     * @testCase getMessage
     */
    public function testGetMessage(): void
    {
        $expectedMessage               = 'Test message';
        $numverifyApiResponseException = new NumverifyApiResponseException($expectedMessage);

        $message = $numverifyApiResponseException->getMessage();
        self::assertSame($expectedMessage, $message);
    }
}
