<?php

namespace Numverify\Exception;

use RuntimeException;
use stdClass;

/**
 * Thrown when the Numverify API returns an API response that is unexpected.
 */
class NumverifyApiResponseException extends RuntimeException
{
    public function __construct(string $message, private readonly ?stdClass $phoneNumberData = null) // @phpstan-ignore-line
    {
        parent::__construct($message);
    }
}
