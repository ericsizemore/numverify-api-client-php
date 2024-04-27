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

namespace Numverify\Exception;

use RuntimeException;

/**
 * Thrown when the Numverify API returns an API response that is unexpected.
 *
 * @see \Numverify\Tests\Exception\NumverifyApiResponseExceptionTest
 */
class NumverifyApiResponseException extends RuntimeException {}
