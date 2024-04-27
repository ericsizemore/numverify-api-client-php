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

use stdClass;

/**
 * PhoneNumber Factory.
 * Role: Factory class to create the appropriate PhoneNumber object.
 */
class Factory
{
    public static function create(stdClass $validatedPhoneNumber): PhoneNumberInterface
    {
        if ((bool) $validatedPhoneNumber->valid === false) {
            return new InvalidPhoneNumber($validatedPhoneNumber);
        }

        return new ValidPhoneNumber($validatedPhoneNumber);
    }
}
