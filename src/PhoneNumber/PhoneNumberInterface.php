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

namespace Numverify\PhoneNumber;

use JsonSerializable;
use Stringable;

/**
 * Interface for all phone numbers returned from the Numverify validate API.
 */
interface PhoneNumberInterface extends JsonSerializable, Stringable
{
    /**
     * Get the phone number.
     */
    public function getNumber(): string;

    /**
     * Is the phone number valid?
     */
    public function isValid(): bool;
}
