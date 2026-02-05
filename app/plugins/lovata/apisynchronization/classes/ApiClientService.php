<?php namespace Lovata\ApiSynchronization\classes;

use Arr;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiClientService
{
    /** @var Client */
    protected $http;

    /** @var string */
    protected $baseUrl;

    /** @var string|null */
    protected $token;

    public function __construct()
    {
        $baseUrl = config('api.base_url');
        \Log::info($baseUrl);

        $this->baseUrl = $baseUrl ? rtrim($baseUrl, '/') : '';
        $this->http = new Client([
            'base_uri'    => $this->baseUrl,
            // Remote host may use self-signed cert in staging; disable verification if needed.
            'verify'      => (bool) config('api.verify_ssl', false),
            'timeout'     => (int) config('api.timeout', 300),
            'http_errors' => false,
        ]);
    }

    /**
     * Set SSL verification for requests (default is taken from env in constructor).
     */
    public function setVerifySsl(bool $verify): void
    {
        $this->http = new Client([
            'base_uri'    => $this->baseUrl,
            'verify'      => $verify,
            'timeout'     => (int) config('api.timeout', 300),
            'http_errors' => false,
        ]);
    }

    /**
     * Authenticate and store bearer token for subsequent requests.
     *
     * @param string $username
     * @param string $password
     * @return string Bearer token
     * @throws \RuntimeException
     */
    public function authenticate(): string
    {
        try {
            $username = config('api.username');
            $password = config('api.password');
            $response = $this->http->post('/login', [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'    => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Login request failed: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if ($status >= 400) {
            throw new \RuntimeException('Login failed with status '.$status.': '.$body);
        }

        $token = null;
        if (is_array($data)) {
            $token = $data['token'] ?? $data['access_token'] ?? $data['bearer'] ?? null;
        }
        if (!$token && is_string($body) && strlen($body) > 10 && strpos($response->getHeaderLine('Content-Type'), 'application/json') === false) {
            // Sometimes API might return raw token
            $token = trim($body);
        }

        if (!$token) {
            throw new \RuntimeException('Unable to retrieve bearer token from login response: '.$body);
        }

        $this->token = $token;
        return $this->token;
    }

    /**
     * Generic fetch from the Get endpoint with provided parameters and optional Where.
     *
     * @param string      $table
     * @param int         $page
     * @param int         $rows
     * @param string|null $where
     * @return array Decoded JSON response
     * @throws \RuntimeException
     */
    public function fetch(string $table, int $page = 1, int $rows = 20, string $where = null): array
    {
        if (!$this->token) {
            throw new \RuntimeException('Not authenticated: call authenticate() first.');
        }

        $query = [
            'Table' => $table,
            'Page'  => $page,
            'Rows'  => $rows,
        ];
        if ($where !== null && $where !== '') {
            $query['Where'] = $where;
        }

        try {
            $response = $this->http->get('/Get', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer '.$this->token,
                ],
                'query'   => $query,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Fetch request failed: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status >= 400) {
            throw new \RuntimeException('Fetch failed with status '.$status.': '.$body);
        }

        $data = json_decode($body, true);
        if ($data === null) {
            // Not JSON? Return as text.
            return ['raw' => $body];
        }

        return $data;
    }

    /**
     * Backwards compatible method name for initial implementation (products-specific).
     */
    public function fetchProducts(string $table, int $page = 1, int $rows = 20): array
    {
        return $this->fetch($table, $page, $rows);
    }

    /**
     * Generic POST to the Post endpoint with provided parameters.
     *
     * @param string $table
     * @param string $where
     * @param array  $jsonRow
     * @param int    $orderRow
     * @param int    $operationPost
     * @return array Decoded JSON response
     * @throws \RuntimeException
     */
    public function post(string $table, string $where, array $jsonRow, int $orderRow = 0, int $operationPost = 0): array
    {
        if (!$this->token) {
            throw new \RuntimeException('Not authenticated: call authenticate() first.');
        }

        $payload = [
            [
                'table'         => $table,
                'where'         => $where,
                'jsonRow'       => json_encode($jsonRow),
                'orderRow'      => $orderRow,
                'operationPost' => $operationPost,
            ]
        ];

        try {
            $response = $this->http->post('/api/v2/Post', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer '.$this->token,
                ],
                'json'    => ['data' => $payload],
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('POST request failed: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status >= 400 || empty($body)) {
            throw new \RuntimeException('POST failed with status '.$status.': '.$body);
        }

        $fullResponse = json_decode($body, true);
        $data = Arr::get($fullResponse, 'data.0.jsonRow');

        return json_decode($data, true);
    }

    /**
     * @param string $table
     * @param string $param
     * @return array
     */
    public function getStored(string $table, string $param): array
    {
        if (!$this->token) {
            $this->authenticate();
        }

        try {
            $response = $this->http->get('/api/v2/StoredGet', [
                'timeout' => 5,
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer '.$this->token,
                ],
                'query'   => [
                    'table' => $table,
                    'param' => $param,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Fetch request failed: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status >= 400) {
            throw new \RuntimeException('Fetch failed with status '.$status.': '.$body);
        }

        $fullResponse = json_decode($body, true);
        $data = Arr::get($fullResponse, 'result.0');

        return $data;
    }

    /**
     * Generic GET request to API endpoint
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return array
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        if (!$this->token) {
            $this->authenticate();
        }

        try {
            $response = $this->http->get('/api/v2/' . ltrim($endpoint, '/'), [
                'timeout' => 30,
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer '.$this->token,
                ],
                'query' => $queryParams,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('GET request failed: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status >= 400) {
            throw new \RuntimeException('GET failed with status '.$status.': '.$body);
        }

        return json_decode($body, true) ?: [];
    }

    /**
     * Iterate pages and return a generator yielding each page's decoded array.
     * Stops after totRows covered or maxPages reached.
     *
     * @param string      $table
     * @param int         $rows
     * @param string|null $where
     * @param int|null    $maxPages
     * @return \Generator
     */
    public function paginate(string $table, int $rows = 100, string $where = null, int $maxPages = null, int $maxItems = null)
    {
        $page = 1;
        $fetched = 0;
        while (true) {
            if ($maxItems !== null && $fetched >= $maxItems) {
                break;
            }
            $data = $this->fetch($table, $page, $rows, $where);
            yield $data;

            $totRows = (int) ($data['totRows'] ?? 0);
            $count = is_array($data['result'] ?? null) ? count($data['result']) : 0;
            $fetched += $count;

            if ($count === 0) {
                break;
            }
            if ($maxPages !== null && $page >= $maxPages) {
                break;
            }
            if ($totRows > 0 && $fetched >= $totRows) {
                break;
            }
            if ($maxItems !== null && $fetched >= $maxItems) {
                break;
            }
            $page++;
        }
    }
}
