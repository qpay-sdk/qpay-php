<?php

declare(strict_types=1);

namespace QPay;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use QPay\Exceptions\QPayException;
use QPay\Models\CreateEbarimtInvoiceRequest;
use QPay\Models\CreateEbarimtRequest;
use QPay\Models\CreateInvoiceRequest;
use QPay\Models\CreateSimpleInvoiceRequest;
use QPay\Models\EbarimtResponse;
use QPay\Models\InvoiceResponse;
use QPay\Models\PaymentCancelRequest;
use QPay\Models\PaymentCheckRequest;
use QPay\Models\PaymentCheckResponse;
use QPay\Models\PaymentDetail;
use QPay\Models\PaymentListRequest;
use QPay\Models\PaymentListResponse;
use QPay\Models\PaymentRefundRequest;
use QPay\Models\TokenResponse;

class QPayClient
{
    private const TOKEN_BUFFER_SECONDS = 30;

    private HttpClient $http;

    private string $accessToken = '';
    private string $refreshToken = '';
    private int $expiresAt = 0;
    private int $refreshExpiresAt = 0;

    public function __construct(
        private readonly Config $config,
        ?HttpClient $http = null,
    ) {
        $this->http = $http ?? new HttpClient([
            'timeout' => 30,
        ]);
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    /**
     * Authenticate with QPay using Basic Auth and return a new token pair.
     *
     * @throws QPayException
     */
    public function getToken(): TokenResponse
    {
        $token = $this->getTokenRequest();
        $this->storeToken($token);

        return $token;
    }

    /**
     * Use the current refresh token to obtain a new access token.
     *
     * @throws QPayException
     */
    public function refreshTokenRequest(): TokenResponse
    {
        $token = $this->doRefreshTokenHttp($this->refreshToken);
        $this->storeToken($token);

        return $token;
    }

    // -------------------------------------------------------------------------
    // Invoice
    // -------------------------------------------------------------------------

    /**
     * Create a detailed invoice with full options.
     * POST /v2/invoice
     *
     * @throws QPayException
     */
    public function createInvoice(CreateInvoiceRequest $request): InvoiceResponse
    {
        $data = $this->doRequest('POST', '/v2/invoice', $request->toArray());

        return InvoiceResponse::fromArray($data);
    }

    /**
     * Create a simple invoice with minimal fields.
     * POST /v2/invoice
     *
     * @throws QPayException
     */
    public function createSimpleInvoice(CreateSimpleInvoiceRequest $request): InvoiceResponse
    {
        $data = $this->doRequest('POST', '/v2/invoice', $request->toArray());

        return InvoiceResponse::fromArray($data);
    }

    /**
     * Create an invoice with ebarimt (tax) information.
     * POST /v2/invoice
     *
     * @throws QPayException
     */
    public function createEbarimtInvoice(CreateEbarimtInvoiceRequest $request): InvoiceResponse
    {
        $data = $this->doRequest('POST', '/v2/invoice', $request->toArray());

        return InvoiceResponse::fromArray($data);
    }

    /**
     * Cancel an existing invoice by ID.
     * DELETE /v2/invoice/{id}
     *
     * @throws QPayException
     */
    public function cancelInvoice(string $invoiceId): void
    {
        $this->doRequest('DELETE', '/v2/invoice/' . $invoiceId);
    }

    // -------------------------------------------------------------------------
    // Payment
    // -------------------------------------------------------------------------

    /**
     * Retrieve payment details by payment ID.
     * GET /v2/payment/{id}
     *
     * @throws QPayException
     */
    public function getPayment(string $paymentId): PaymentDetail
    {
        $data = $this->doRequest('GET', '/v2/payment/' . $paymentId);

        return PaymentDetail::fromArray($data);
    }

    /**
     * Check if a payment has been made for an invoice.
     * POST /v2/payment/check
     *
     * @throws QPayException
     */
    public function checkPayment(PaymentCheckRequest $request): PaymentCheckResponse
    {
        $data = $this->doRequest('POST', '/v2/payment/check', $request->toArray());

        return PaymentCheckResponse::fromArray($data);
    }

    /**
     * Return a list of payments matching the given criteria.
     * POST /v2/payment/list
     *
     * @throws QPayException
     */
    public function listPayments(PaymentListRequest $request): PaymentListResponse
    {
        $data = $this->doRequest('POST', '/v2/payment/list', $request->toArray());

        return PaymentListResponse::fromArray($data);
    }

    /**
     * Cancel a payment (card transactions only).
     * DELETE /v2/payment/cancel/{id}
     *
     * @throws QPayException
     */
    public function cancelPayment(string $paymentId, PaymentCancelRequest $request): void
    {
        $this->doRequest('DELETE', '/v2/payment/cancel/' . $paymentId, $request->toArray());
    }

    /**
     * Refund a payment (card transactions only).
     * DELETE /v2/payment/refund/{id}
     *
     * @throws QPayException
     */
    public function refundPayment(string $paymentId, PaymentRefundRequest $request): void
    {
        $this->doRequest('DELETE', '/v2/payment/refund/' . $paymentId, $request->toArray());
    }

    // -------------------------------------------------------------------------
    // Ebarimt
    // -------------------------------------------------------------------------

    /**
     * Create an ebarimt (electronic tax receipt) for a payment.
     * POST /v2/ebarimt_v3/create
     *
     * @throws QPayException
     */
    public function createEbarimt(CreateEbarimtRequest $request): EbarimtResponse
    {
        $data = $this->doRequest('POST', '/v2/ebarimt_v3/create', $request->toArray());

        return EbarimtResponse::fromArray($data);
    }

    /**
     * Cancel an ebarimt by payment ID.
     * DELETE /v2/ebarimt_v3/{id}
     *
     * @throws QPayException
     */
    public function cancelEbarimt(string $paymentId): EbarimtResponse
    {
        $data = $this->doRequest('DELETE', '/v2/ebarimt_v3/' . $paymentId);

        return EbarimtResponse::fromArray($data);
    }

    // -------------------------------------------------------------------------
    // Internal: Token management
    // -------------------------------------------------------------------------

    /**
     * Ensure a valid access token is available, refreshing or re-authenticating as needed.
     *
     * @throws QPayException
     */
    private function ensureToken(): void
    {
        $now = time();

        // Access token still valid
        if ($this->accessToken !== '' && $now < $this->expiresAt - self::TOKEN_BUFFER_SECONDS) {
            return;
        }

        // Try refresh if possible
        $canRefresh = $this->refreshToken !== '' && $now < $this->refreshExpiresAt - self::TOKEN_BUFFER_SECONDS;

        if ($canRefresh) {
            try {
                $token = $this->doRefreshTokenHttp($this->refreshToken);
                $this->storeToken($token);
                return;
            } catch (QPayException) {
                // Refresh failed, fall through to get new token
            }
        }

        // Both expired or no tokens, get new token
        $token = $this->getTokenRequest();
        $this->storeToken($token);
    }

    /**
     * Perform Basic Auth token request.
     *
     * @throws QPayException
     */
    private function getTokenRequest(): TokenResponse
    {
        return $this->doBasicAuthRequest('POST', '/v2/auth/token');
    }

    /**
     * Perform token refresh HTTP call.
     *
     * @throws QPayException
     */
    private function doRefreshTokenHttp(string $refreshTok): TokenResponse
    {
        $url = $this->config->baseUrl . '/v2/auth/refresh';

        try {
            $response = $this->http->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $refreshTok,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new QPayException(
                statusCode: 0,
                errorCode: 'REQUEST_FAILED',
                errorMessage: $e->getMessage(),
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            $this->throwQPayException($statusCode, $body);
        }

        $data = json_decode($body, true) ?? [];

        return TokenResponse::fromArray($data);
    }

    private function storeToken(TokenResponse $token): void
    {
        $this->accessToken = $token->accessToken;
        $this->refreshToken = $token->refreshToken;
        $this->expiresAt = $token->expiresIn;
        $this->refreshExpiresAt = $token->refreshExpiresIn;
    }

    // -------------------------------------------------------------------------
    // Internal: HTTP helpers
    // -------------------------------------------------------------------------

    /**
     * Execute an authenticated API request (Bearer token).
     *
     * @throws QPayException
     */
    private function doRequest(string $method, string $path, ?array $body = null): array
    {
        $this->ensureToken();

        $url = $this->config->baseUrl . $path;

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        try {
            $response = $this->http->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new QPayException(
                statusCode: 0,
                errorCode: 'REQUEST_FAILED',
                errorMessage: $e->getMessage(),
            );
        }

        $statusCode = $response->getStatusCode();
        $responseBody = (string) $response->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            $this->throwQPayException($statusCode, $responseBody);
        }

        if ($responseBody === '') {
            return [];
        }

        return json_decode($responseBody, true) ?? [];
    }

    /**
     * Execute a Basic Auth request (for token endpoints).
     *
     * @throws QPayException
     */
    private function doBasicAuthRequest(string $method, string $path): TokenResponse
    {
        $url = $this->config->baseUrl . $path;

        try {
            $response = $this->http->request($method, $url, [
                'auth' => [$this->config->username, $this->config->password],
            ]);
        } catch (GuzzleException $e) {
            throw new QPayException(
                statusCode: 0,
                errorCode: 'REQUEST_FAILED',
                errorMessage: $e->getMessage(),
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            $this->throwQPayException($statusCode, $body);
        }

        $data = json_decode($body, true) ?? [];

        return TokenResponse::fromArray($data);
    }

    /**
     * Parse error response and throw QPayException.
     *
     * @throws QPayException
     * @return never
     */
    private function throwQPayException(int $statusCode, string $body): void
    {
        $data = json_decode($body, true) ?? [];

        $errorCode = $data['error'] ?? '';
        $errorMessage = $data['message'] ?? '';

        if ($errorCode === '') {
            $errorCode = $this->httpStatusText($statusCode);
        }
        if ($errorMessage === '') {
            $errorMessage = $body;
        }

        throw new QPayException(
            statusCode: $statusCode,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
            rawBody: $body,
        );
    }

    /**
     * Get a human-readable text for common HTTP status codes.
     */
    private function httpStatusText(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'HTTP ' . $code,
        };
    }
}
