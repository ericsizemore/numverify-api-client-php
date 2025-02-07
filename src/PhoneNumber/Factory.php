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
 *
 * Role: Factory class to create the appropriate PhoneNumber object.
 *
 * @phpstan-type ValidPhoneNumberObject = stdClass&object{
 *     valid: bool|string,
 *     number: int|string,
 *     local_format: string,
 *     international_format: string,
 *     country_prefix: string,
 *     country_code: string,
 *     country_name: string,
 *     location: string,
 *     carrier: string,
 *     line_type: string
 * }
 * @phpstan-type InvalidPhoneNumberObject = stdClass&object{valid: bool|string, number: int|string}
 */
class Factory
{
    /**
     * @param InvalidPhoneNumberObject|ValidPhoneNumberObject $validatedPhoneNumber
     */
    public static function create(stdClass $validatedPhoneNumber): PhoneNumberInterface
    {
        if ((bool) $validatedPhoneNumber->valid === false) {
            /**
             * @var InvalidPhoneNumberObject $validatedPhoneNumber
             */
            return new InvalidPhoneNumber($validatedPhoneNumber);
        }

        /**
         * @var ValidPhoneNumberObject $validatedPhoneNumber
         */
        return new ValidPhoneNumber($validatedPhoneNumber);
    }
}
