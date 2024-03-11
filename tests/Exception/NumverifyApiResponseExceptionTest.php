<?php

namespace Numverify\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Numverify\Exception\NumverifyApiResponseException;
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
        $phoneNumberData               = new stdClass();
        $numverifyApiResponseException = new NumverifyApiResponseException($expectedMessage, $phoneNumberData);

        $message = $numverifyApiResponseException->getMessage();
        self::assertSame($expectedMessage, $message);
    }
}
