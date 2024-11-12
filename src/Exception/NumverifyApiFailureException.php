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

namespace Numverify\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use stdClass;

use function json_decode;

/**
 * Thrown when the Numverify API returns a failure response.
 *
 * @see \Numverify\Tests\Exception\NumverifyApiFailureExceptionTest
 */
class NumverifyApiFailureException extends RuntimeException
{
    private readonly string $body;

    private readonly string $reasonPhrase;
    private readonly int $statusCode;

    public function __construct(ResponseInterface $response)
    {
        $this->statusCode   = $response->getStatusCode();
        $this->reasonPhrase = $response->getReasonPhrase();
        $this->body         = (string) $response->getBody();

        $message = $this->parseMessageFromBody($this->body);

        parent::__construct($message);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Parse JSON body error message.
     *
     * Expecting a JSON body like:
     * {
     *     "success":false,
     *     "error":{
     *         "code":101,
     *         "type":"invalid_access_key",
     *         "info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"
     *     }
     * }
     */
    private function parseMessageFromBody(string $jsonBody): string
    {
        /** @var stdClass $body */
        $body = json_decode($jsonBody);

        if (!isset($body->error)) {
            return \sprintf('Unknown error - %d %s', $this->statusCode, $this->getReasonPhrase());
        }

        /** @var object{type: string, code: int, info: string} $error */
        $error = $body->error;

        return \sprintf('Type:%s Code:%d Info:%s', $error->type, $error->code, $error->info);
    }
}
