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

namespace Numverify\Exception;

use RuntimeException;

/**
 * Thrown when the Numverify API returns an API response that is unexpected.
 *
 * @see \Numverify\Tests\Exception\NumverifyApiResponseExceptionTest
 */
class NumverifyApiResponseException extends RuntimeException {}
