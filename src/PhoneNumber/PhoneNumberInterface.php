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

namespace Numverify\PhoneNumber;

/**
 * Interface for all phone numbers returned from the Numverify validate API.
 */
interface PhoneNumberInterface
{
    public function isValid(): bool;

    public function getNumber(): string;
}
