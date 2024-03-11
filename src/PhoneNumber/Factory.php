<?php

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
