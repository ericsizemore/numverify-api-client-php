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

namespace Numverify\Tests\Exception;

use GuzzleHttp\Psr7\Response;
use Numverify\Exception\NumverifyApiFailureException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class NumverifyApiFailureExceptionTest extends TestCase
{
    private const STATUS_CODE = 500;

    private const REASON_PHRASE = 'Internal Server Error';

    private const BODY = 'server error';

    private Response $response;

    protected function setUp(): void
    {
        $this->response = new Response(self::STATUS_CODE, body: self::BODY, reason: self::REASON_PHRASE);
    }

    #[TestDox('NumverifyApiFailureException can return status code.')]
    public function testGetStatusCode(): void
    {
        $numverifyApiFailureException = new NumverifyApiFailureException($this->response);

        $statusCode = $numverifyApiFailureException->getStatusCode();
        self::assertSame(self::STATUS_CODE, $statusCode);
    }

    #[TestDox('NumverifyApiFailureException can return reason phrase.')]
    public function testGetReasonPhrase(): void
    {
        $numverifyApiFailureException = new NumverifyApiFailureException($this->response);

        $reasonPhrase = $numverifyApiFailureException->getReasonPhrase();
        self::assertSame(self::REASON_PHRASE, $reasonPhrase);
    }

    #[TestDox('NumverifyApiFailureException can return body.')]
    public function testGetBody(): void
    {
        $numverifyApiFailureException = new NumverifyApiFailureException($this->response);

        $body = $numverifyApiFailureException->getBody();
        self::assertSame(self::BODY, $body);
    }
}
