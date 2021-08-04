<?php
/**
 * (c) Wessel Strengholt <wessel.strengholt@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shivella\Bitly\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Shivella\Bitly\Exceptions\AccessDeniedException;
use Shivella\Bitly\Exceptions\InvalidResponseException;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;
use function json_encode;

/**
 * Class BitlyClient
 */
class BitlyClient
{
    /** @var ClientInterface */
    private $client;

    /** @var string $token */
    private $token;
    /**
     * @var string|null
     */
    private $customDomain;

    /**
     * @param ClientInterface $client
     * @param string          $token
     * @param string|null     $customDomain
     */
    public function __construct(ClientInterface $client, string $token, ?string $customDomain = null)
    {
        $this->client = $client;
        $this->token  = $token;
        $this->customDomain = $customDomain;
    }

    /**
     * @param string $url raw URL.
     *
     * @throws \Shivella\Bitly\Exceptions\AccessDeniedException
     * @throws \Shivella\Bitly\Exceptions\InvalidResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return string shorten URL.
     */
    public function getUrl(string $url): string
    {
        $requestUrl = 'https://api-ssl.bitly.com/v4/shorten';

        $header = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type'  => 'application/json',
        ];

        $requestData = ['long_url' => $url];
        if ($this->customDomain !== null) {
            $requestData['domain'] = $this->customDomain;
        }

        try {
            $request = new Request('POST', $requestUrl, $header, json_encode($requestData));

            $response = $this->client->send($request);
        } catch (RequestException $e) {
            if ($e->getResponse() !== null && $e->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN) {
                throw new AccessDeniedException('Invalid access token.', $e->getCode(), $e);
            }

            throw new InvalidResponseException($e->getMessage(), $e->getCode(), $e);
        }

        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();

        if ($statusCode === Response::HTTP_FORBIDDEN) {
            throw new AccessDeniedException('Invalid access token.');
        }

        if ( ! in_array($statusCode, [Response::HTTP_OK, Response::HTTP_CREATED])) {
            throw new InvalidResponseException('The API does not return a 200 or 201 status code. Response: '.$content);
        }

        $data = json_decode($content, true);

        if (isset($data['link'])) {
            return $data['link'];
        }

        if (isset($data['data']['link'])) {
            return $data['data']['link'];
        }

        throw new InvalidResponseException('The response does not contain a shortened link. Response: '.$content);
    }
}
