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

namespace Numverify\Exception;

use RuntimeException;
use Psr\Http\Message\ResponseInterface;

use function json_decode;
use function sprintf;

use stdClass;

/**
 * Thrown when the Numverify API returns a failure response.
 */
class NumverifyApiFailureException extends RuntimeException
{
    private readonly int $statusCode;

    private readonly string $reasonPhrase;

    private readonly string $body;

    public function __construct(ResponseInterface $response)
    {
        $this->statusCode   = $response->getStatusCode();
        $this->reasonPhrase = $response->getReasonPhrase();
        $this->body         = (string) $response->getBody();

        $message = $this->parseMessageFromBody($this->body);

        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getBody(): string
    {
        return $this->body;
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
            return sprintf('Unknown error - %d %s', $this->statusCode, $this->getReasonPhrase());
        }

        /** @var stdClass $error */
        $error = $body->error;

        return sprintf('Type:%s Code:%d Info:%s', (string) $error->type, (int) $error->code, (string) $error->info);
    }
}
