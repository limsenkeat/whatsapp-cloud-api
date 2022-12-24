<?php

namespace Netflie\WhatsAppCloudApi;

use Netflie\WhatsAppCloudApi\Response\ResponseException;

class Response
{
    /**
     * @var int The HTTP status code response from Graph.
     */
    protected int $http_status_code;

    /**
     * @var array The headers returned from Graph.
     */
    protected array $headers;

    /**
     * @var string The raw body of the response from Graph.
     */
    protected string $body;

    /**
     * @var array The decoded body of the Graph response.
     */
    protected array $decoded_body = [];

    /**
     * @var Request The original request that returned this response.
     */
    protected Request $request;

    /**
     * Creates a new Response entity.
     *
     * @param Request $request
     * @param string     $body
     * @param int|null        $http_status_code
     * @param array|null      $headers
     */
    public function __construct(Request $request, string $body, ?int $http_status_code = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = $body;
        $this->http_status_code = $http_status_code;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Return the original request that returned this response.
     *
     * @return Resquest
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Return the access token that was used for this response.
     *
     * @return string
     */
    public function accessToken(): string
    {
        return $this->request->accessToken();
    }

    /**
     * Return the HTTP status code for this response.
     *
     * @return int
     */
    public function httpStatusCode(): int
    {
        return $this->http_status_code;
    }

    /**
     * Return the HTTP headers for this response.
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     *
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     *
     * @return array
     */
    public function decodedBody(): array
    {
        return $this->decoded_body;
    }

    /**
     * Get the version of Graph that returned this response.
     *
     * @return string|null
     */
    public function graphVersion(): ?string
    {
        return $this->headers['facebook-api-version'] ?? null;
    }

    /**
     * Returns true if Graph returned an error message.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return isset($this->decoded_body['error']);
    }

    /**
     * Throws the exception.
     *
     * @throws ResponseException
     */
    public function throwException()
    {
        throw new ResponseException($this);
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     */
    public function decodeBody(): void
    {
        $this->decoded_body = json_decode($this->body, true);

        if ($this->decoded_body === null) {
            $this->decoded_body = [];
            parse_str($this->body, $this->decoded_body);
        } elseif (is_numeric($this->decoded_body)) {
            $this->decoded_body = ['id' => $this->decoded_body];
        }

        if (!is_array($this->decoded_body)) {
            $this->decoded_body = [];
        }
    }
}
