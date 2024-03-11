<?php

namespace Numverify\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Numverify\Exception\NumverifyApiFailureException;
use GuzzleHttp\Psr7\Response;

/**
 * @internal
 */
class NumverifyApiFailureExceptionTest extends TestCase
{
    private const STATUS_CODE   = 500;

    private const REASON_PHRASE = 'Internal Server Error';

    private const BODY          = 'server error';

    private Response $response;

    protected function setUp(): void
    {
        $this->response = new Response(self::STATUS_CODE, body: self::BODY, reason: self::REASON_PHRASE);
    }

    /**
     * @testCase getStatusCode
     */
    public function testGetStatusCode(): void
    {
        $numverifyApiFailureException = new NumverifyApiFailureException($this->response);

        $statusCode = $numverifyApiFailureException->getStatusCode();
        self::assertSame(self::STATUS_CODE, $statusCode);
    }

    /**
     * @testCase getReasonPhrase
     */
    public function testGetReasonPhrase(): void
    {
        $numverifyApiFailureException = new NumverifyApiFailureException($this->response);

        $reasonPhrase = $numverifyApiFailureException->getReasonPhrase();
        self::assertSame(self::REASON_PHRASE, $reasonPhrase);
    }

    /**
     * @testCase getBody
     */
    public function testGetBody(): void
    {
        $numverifyApiFailureException = new NumverifyApiFailureException($this->response);

        $body = $numverifyApiFailureException->getBody();
        self::assertSame(self::BODY, $body);
    }
}
