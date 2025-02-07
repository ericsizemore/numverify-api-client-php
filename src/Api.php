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

namespace Numverify;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr6CacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Numverify\Country\Collection;
use Numverify\Country\Country;
use Numverify\Exception\NumverifyApiFailureException;
use Numverify\PhoneNumber\Factory;
use Numverify\PhoneNumber\PhoneNumberInterface;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;
use stdClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use function array_keys;
use function array_map;
use function array_merge;
use function is_dir;
use function is_writable;
use function json_decode;
use function trim;

/**
 * Main API class.
 *
 * @see Tests\ApiTest
 *
 * @psalm-api
 *
 * @phpstan-type ApiJsonArray array{
 *     success?: bool,
 *     error?: array{code?: int, type?: string, info?: string},
 *     valid?: bool,
 *     number?: string,
 *     local_format?: string,
 *     international_format?: string,
 *     country_prefix?: string,
 *     country_code?: string,
 *     country_name?: string,
 *     location?: string,
 *     carrier?: string,
 *     line_type?: string
 * }
 * @phpstan-type ApiCountryJsonArray array<string, array{country_name: string, dialling_code: string}>
 *
 * @phpstan-import-type InvalidPhoneNumberObject from PhoneNumber\Factory
 * @phpstan-import-type ValidPhoneNumberObject from PhoneNumber\Factory
 */
class Api
{
    /**
     * Current version of the Numverify package.
     *
     * @var string
     */
    public const LIBRARY_VERSION = '3.0.1';

    /**
     * URL for Numverify's "Free" plan.
     *
     * @see https://numverify.com/product
     */
    private const HTTP_URL = 'http://apilayer.net/api';

    /**
     * URL for Numverify's paid plans. ("Basic", "Professional", or "Enterprise").
     *
     * @see https://numverify.com/product
     */
    private const HTTPS_URL = 'https://apilayer.net/api';

    /**
     * Guzzle Client.
     */
    private ClientInterface $client;

    /**
     * Api constructor.
     *
     * Requires an access key. You can get one from Numverify.
     *
     * @see https://numverify.com/product
     *
     * Note: If you are on their free plan, $useHttps = true will not work for you.
     *
     * @param string               $accessKey API access key.
     * @param bool                 $useHttps  (optional) Flag to determine if API calls should use http or https.
     * @param null|ClientInterface $client    (optional) Parameter to provide your own Guzzle client.
     * @param array<string, mixed> $options   (optional) Array of options to pass to the Guzzle client.
     */
    public function __construct(
        #[SensitiveParameter]
        private readonly string $accessKey,
        bool $useHttps = false,
        ?ClientInterface $client = null,
        array $options = []
    ) {
        // If we already have a client.
        if ($client instanceof ClientInterface) {
            $this->client = $client;

            return;
        }

        // Build client
        $clientOptions = ['base_uri' => $this->getUrl($useHttps)];

        // If $options has 'cachePath' key, and it is a valid directory, then buildCacheHandler() will
        // add Cache to the Guzzle handler stack.
        $options = $this->buildCacheHandler($options);

        // Merge $options into main client options.
        $clientOptions = array_merge($clientOptions, $options);

        $this->client = new Client($clientOptions);
    }

    /**
     * Get list of countries.
     *
     * @throws NumverifyApiFailureException If the response is non 200 or success field is false.
     * @throws GuzzleException              If Guzzle encounters an issue.
     */
    public function getCountries(): Collection
    {
        try {
            $response = $this->client->request('GET', '/countries', [
                'query' => [
                    'access_key' => $this->accessKey,
                ],
            ]);
        } catch (ServerException $serverException) {
            // >= 400 <= 500 status code
            // wrap ServerException with NumverifyApiFailureException
            throw new NumverifyApiFailureException($serverException->getResponse());
        }

        /** @var ApiCountryJsonArray $body */
        $body = $this->validateAndDecodeResponse($response, true);

        $countries = array_map(
            static fn (array $country, string $countryCode): Country => new Country($countryCode, $country['country_name'], $country['dialling_code']),
            $body,
            array_keys($body)
        );

        return new Collection(...$countries);
    }

    /**
     * Validate a phone number.
     *
     * Will return ValidPhoneNumber for a valid number, InvalidPhoneNumber otherwise (both PhoneNumberInterface).
     *
     * @param string $countryCode (Optional) Use to provide a phone number in a local format (non E.164).
     *
     * @throws NumverifyApiFailureException If the response is non 200 or success field is false.
     * @throws GuzzleException              If Guzzle encounters an issue.
     */
    public function validatePhoneNumber(string $phoneNumber, string $countryCode = ''): PhoneNumberInterface
    {
        $phoneNumber = trim($phoneNumber);
        $countryCode = trim($countryCode);

        $query = [
            'access_key' => $this->accessKey,
            'number'     => $phoneNumber,
        ];

        if ($countryCode !== '') {
            $query['country_code'] = $countryCode;
        }

        try {
            $result = $this->client->request('GET', '/validate', [
                'query' => $query,
            ]);
        } catch (ServerException $serverException) {
            // >= 400 <= 500 status code
            // wrap ServerException with NumverifyApiFailureException
            throw new NumverifyApiFailureException($serverException->getResponse());
        }

        $body = $this->validateAndDecodeResponse($result);

        return Factory::create($body);
    }

    /**
     * Creates a Guzzle HandlerStack and adds the CacheMiddleware if 'cachePath' exists within the
     * given $options array, and it is a valid directory.
     *
     * Returns given $options as passed, minus the 'cachePath' as it is not a valid Guzzle option for
     * the client.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function buildCacheHandler(array $options): array
    {
        $cachePath = (string) ($options['cachePath'] ?? null); // @phpstan-ignore-line

        if (is_dir($cachePath) && is_writable($cachePath)) {
            $handlerStack = HandlerStack::create();
            $handlerStack->push(middleware: new CacheMiddleware(cacheStrategy: new PrivateCacheStrategy(
                cache: new Psr6CacheStorage(cachePool: new FilesystemAdapter(namespace: 'numverify', defaultLifetime: 300, directory: $cachePath))
            )), name: 'cache');

            unset($options['cachePath']);
            $options += ['handler' => $handlerStack];
        }

        return $options;
    }

    /**
     * Get the URL to use for API calls.
     */
    private function getUrl(bool $useHttps): string
    {
        return $useHttps ? self::HTTPS_URL : self::HTTP_URL;
    }

    /**
     * Given a response object, checks the status code and checks the 'success' field, if it exists.
     *
     * If everything looks good, it returns the decoded jSON data based on $asArray.
     *
     * @param bool $asArray If true, returns the decoded jSON as an assoc. array, stdClass otherwise.
     *
     * @throws NumverifyApiFailureException If the response is non 200 or success field is false.
     *
     * @return ($asArray is true ? ApiCountryJsonArray|ApiJsonArray : InvalidPhoneNumberObject|ValidPhoneNumberObject)
     */
    private function validateAndDecodeResponse(ResponseInterface $response, bool $asArray = false): array|stdClass
    {
        // If not 200 ok
        if ($response->getStatusCode() !== 200) {
            throw new NumverifyApiFailureException($response);
        }

        /**
         * @var ApiCountryJsonArray|ApiJsonArray|InvalidPhoneNumberObject|ValidPhoneNumberObject $body
         */
        $body = json_decode($response->getBody()->getContents(), $asArray);

        if (!$this->verifySuccess($body)) {
            throw new NumverifyApiFailureException($response);
        }

        return $body;
    }

    /**
     * @param ApiCountryJsonArray|ApiJsonArray|InvalidPhoneNumberObject|ValidPhoneNumberObject $body
     */
    private function verifySuccess(array|stdClass $body): bool
    {
        if (!\is_array($body)) {
            $body = (array) $body;
        }

        return !(isset($body['success']) && $body['success'] === false);
    }
}
