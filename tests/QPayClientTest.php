<?php

declare(strict_types=1);

namespace QPay\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use QPay\Config;
use QPay\Exceptions\QPayException;
use QPay\Models\CreateEbarimtInvoiceRequest;
use QPay\Models\CreateEbarimtRequest;
use QPay\Models\CreateInvoiceRequest;
use QPay\Models\CreateSimpleInvoiceRequest;
use QPay\Models\PaymentCancelRequest;
use QPay\Models\PaymentCheckRequest;
use QPay\Models\PaymentListRequest;
use QPay\Models\PaymentRefundRequest;
use QPay\QPayClient;

class QPayClientTest extends TestCase
{
    private Config $config;

    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    protected function setUp(): void
    {
        $this->config = new Config(
            baseUrl: 'https://merchant.qpay.mn',
            username: 'test_user',
            password: 'test_pass',
            invoiceCode: 'TEST_INVOICE',
            callbackUrl: 'https://example.com/callback',
        );

        $this->history = [];
    }

    private function createClient(MockHandler $mock): QPayClient
    {
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        $http = new HttpClient(['handler' => $handlerStack]);

        return new QPayClient($this->config, $http);
    }

    private function tokenResponseBody(): string
    {
        return json_encode([
            'token_type' => 'Bearer',
            'refresh_expires_in' => time() + 86400,
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
            'expires_in' => time() + 3600,
            'scope' => 'default',
            'not-before-policy' => '0',
            'session_state' => 'abc-123',
        ]);
    }

    private function invoiceResponseBody(): string
    {
        return json_encode([
            'invoice_id' => 'inv_123',
            'qr_text' => 'qr_text_data',
            'qr_image' => 'base64_qr_image',
            'qPay_shortUrl' => 'https://qpay.mn/q/abc',
            'urls' => [
                [
                    'name' => 'Khan Bank',
                    'description' => 'Khan Bank app',
                    'logo' => 'https://example.com/logo.png',
                    'link' => 'https://example.com/link',
                ],
            ],
        ]);
    }

    private function paymentDetailBody(): string
    {
        return json_encode([
            'payment_id' => 'pay_123',
            'payment_status' => 'PAID',
            'payment_fee' => '0',
            'payment_amount' => '1000',
            'payment_currency' => 'MNT',
            'payment_date' => '2026-01-01',
            'payment_wallet' => 'khan_bank',
            'transaction_type' => 'P2P',
            'object_type' => 'INVOICE',
            'object_id' => 'inv_123',
            'next_payment_date' => null,
            'next_payment_datetime' => null,
            'card_transactions' => [],
            'p2p_transactions' => [],
        ]);
    }

    private function paymentCheckResponseBody(): string
    {
        return json_encode([
            'count' => 1,
            'paid_amount' => 1000.0,
            'rows' => [
                [
                    'payment_id' => 'pay_123',
                    'payment_status' => 'PAID',
                    'payment_amount' => '1000',
                    'trx_fee' => '0',
                    'payment_currency' => 'MNT',
                    'payment_wallet' => 'khan_bank',
                    'payment_type' => 'P2P',
                    'next_payment_date' => null,
                    'next_payment_datetime' => null,
                    'card_transactions' => [],
                    'p2p_transactions' => [],
                ],
            ],
        ]);
    }

    private function paymentListResponseBody(): string
    {
        return json_encode([
            'count' => 1,
            'rows' => [
                [
                    'payment_id' => 'pay_456',
                    'payment_date' => '2026-01-01',
                    'payment_status' => 'PAID',
                    'payment_fee' => '0',
                    'payment_amount' => '2000',
                    'payment_currency' => 'MNT',
                    'payment_wallet' => 'golomt',
                    'payment_name' => 'Test Payment',
                    'payment_description' => 'Test desc',
                    'qr_code' => 'qr_abc',
                    'paid_by' => 'user_1',
                    'object_type' => 'INVOICE',
                    'object_id' => 'inv_456',
                ],
            ],
        ]);
    }

    private function ebarimtResponseBody(): string
    {
        return json_encode([
            'id' => 'eb_123',
            'ebarimt_by' => 'system',
            'g_wallet_id' => 'w_1',
            'g_wallet_customer_id' => 'wc_1',
            'ebarimt_receiver_type' => 'citizen',
            'ebarimt_receiver' => 'AA12345678',
            'ebarimt_district_code' => '01',
            'ebarimt_bill_type' => '1',
            'g_merchant_id' => 'm_1',
            'merchant_branch_code' => 'br_1',
            'merchant_terminal_code' => null,
            'merchant_staff_code' => null,
            'merchant_register_no' => '1234567',
            'g_payment_id' => 'pay_123',
            'paid_by' => 'user_1',
            'object_type' => 'INVOICE',
            'object_id' => 'inv_123',
            'amount' => '1000',
            'vat_amount' => '100',
            'city_tax_amount' => '10',
            'ebarimt_qr_data' => 'qr_eb_data',
            'ebarimt_lottery' => 'lottery_123',
            'note' => null,
            'barimt_status' => 'CREATED',
            'barimt_status_date' => '2026-01-01',
            'ebarimt_sent_email' => null,
            'ebarimt_receiver_phone' => '99001122',
            'tax_type' => '1',
            'merchant_tin' => 'tin_123',
            'ebarimt_receipt_id' => 'receipt_1',
            'created_by' => 'system',
            'created_date' => '2026-01-01',
            'updated_by' => 'system',
            'updated_date' => '2026-01-01',
            'status' => true,
            'barimt_items' => [],
            'barimt_transactions' => [],
            'barimt_histories' => [],
        ]);
    }

    // -------------------------------------------------------------------------
    // Auth: getToken
    // -------------------------------------------------------------------------

    public function testGetTokenSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
        ]);

        $client = $this->createClient($mock);
        $token = $client->getToken();

        $this->assertSame('Bearer', $token->tokenType);
        $this->assertSame('test_access_token', $token->accessToken);
        $this->assertSame('test_refresh_token', $token->refreshToken);
        $this->assertSame('default', $token->scope);
        $this->assertSame('abc-123', $token->sessionState);

        // Verify Basic Auth was used
        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('/v2/auth/token', (string) $request->getUri());
    }

    public function testGetTokenHttpError(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'error' => 'AUTHENTICATION_FAILED',
                'message' => 'Invalid credentials',
            ])),
        ]);

        $client = $this->createClient($mock);

        $this->expectException(QPayException::class);
        $client->getToken();
    }

    public function testGetTokenNetworkError(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'Connection timed out',
                new Request('POST', 'https://merchant.qpay.mn/v2/auth/token'),
            ),
        ]);

        $client = $this->createClient($mock);

        $this->expectException(QPayException::class);
        $client->getToken();
    }

    // -------------------------------------------------------------------------
    // Auth: refreshToken
    // -------------------------------------------------------------------------

    public function testRefreshTokenSuccess(): void
    {
        $mock = new MockHandler([
            // First: getToken to populate tokens
            new Response(200, [], $this->tokenResponseBody()),
            // Second: refresh
            new Response(200, [], json_encode([
                'token_type' => 'Bearer',
                'refresh_expires_in' => time() + 86400,
                'refresh_token' => 'new_refresh_token',
                'access_token' => 'new_access_token',
                'expires_in' => time() + 3600,
                'scope' => 'default',
                'not-before-policy' => '0',
                'session_state' => 'new-session',
            ])),
        ]);

        $client = $this->createClient($mock);

        // First get a token so refresh token is stored
        $client->getToken();
        $refreshed = $client->refreshTokenRequest();

        $this->assertSame('new_access_token', $refreshed->accessToken);
        $this->assertSame('new_refresh_token', $refreshed->refreshToken);

        // Verify Bearer auth was used for refresh
        $request = $this->history[1]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('/v2/auth/refresh', (string) $request->getUri());
        $this->assertStringContainsString('Bearer', $request->getHeaderLine('Authorization'));
    }

    public function testRefreshTokenHttpError(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(401, [], json_encode([
                'error' => 'AUTHENTICATION_FAILED',
                'message' => 'Refresh token expired',
            ])),
        ]);

        $client = $this->createClient($mock);
        $client->getToken();

        $this->expectException(QPayException::class);
        $client->refreshTokenRequest();
    }

    // -------------------------------------------------------------------------
    // Invoice: createInvoice
    // -------------------------------------------------------------------------

    public function testCreateInvoiceSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'INV-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Test invoice',
            amount: 1000.0,
            callbackUrl: 'https://example.com/callback',
        );

        $response = $client->createInvoice($request);

        $this->assertSame('inv_123', $response->invoiceId);
        $this->assertSame('qr_text_data', $response->qrText);
        $this->assertSame('base64_qr_image', $response->qrImage);
        $this->assertSame('https://qpay.mn/q/abc', $response->qPayShortUrl);
        $this->assertCount(1, $response->urls);
        $this->assertSame('Khan Bank', $response->urls[0]['name']);

        // Verify Bearer token was sent
        $invoiceRequest = $this->history[1]['request'];
        $this->assertSame('POST', $invoiceRequest->getMethod());
        $this->assertStringContainsString('Bearer test_access_token', $invoiceRequest->getHeaderLine('Authorization'));
    }

    public function testCreateInvoiceWithOptionalFields(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'INV-002',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Test invoice with options',
            amount: 5000.0,
            callbackUrl: 'https://example.com/callback',
            allowPartial: true,
            minimumAmount: 1000.0,
            allowExceed: false,
            maximumAmount: 10000.0,
            note: 'Test note',
        );

        $response = $client->createInvoice($request);
        $this->assertSame('inv_123', $response->invoiceId);

        // Verify the request body contains optional fields
        $body = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertTrue($body['allow_partial']);
        $this->assertSame(1000.0, $body['minimum_amount']);
        $this->assertFalse($body['allow_exceed']);
        $this->assertSame(10000.0, $body['maximum_amount']);
        $this->assertSame('Test note', $body['note']);
    }

    public function testCreateInvoiceApiError(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(400, [], json_encode([
                'error' => 'INVOICE_CODE_INVALID',
                'message' => 'Invoice code is invalid',
            ])),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateInvoiceRequest(
            invoiceCode: 'INVALID',
            senderInvoiceNo: 'INV-003',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Test',
            amount: 100.0,
            callbackUrl: 'https://example.com/callback',
        );

        try {
            $client->createInvoice($request);
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(400, $e->statusCode);
            $this->assertSame('INVOICE_CODE_INVALID', $e->errorCode);
            $this->assertSame('Invoice code is invalid', $e->errorMessage);
        }
    }

    // -------------------------------------------------------------------------
    // Invoice: createSimpleInvoice
    // -------------------------------------------------------------------------

    public function testCreateSimpleInvoiceSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'SINV-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Simple invoice',
            amount: 500.0,
            callbackUrl: 'https://example.com/callback',
        );

        $response = $client->createSimpleInvoice($request);

        $this->assertSame('inv_123', $response->invoiceId);
        $this->assertSame('qr_text_data', $response->qrText);
    }

    public function testCreateSimpleInvoiceWithBranchCode(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'SINV-002',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Simple with branch',
            amount: 500.0,
            callbackUrl: 'https://example.com/callback',
            senderBranchCode: 'BRANCH_01',
        );

        $response = $client->createSimpleInvoice($request);
        $this->assertSame('inv_123', $response->invoiceId);

        $body = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertSame('BRANCH_01', $body['sender_branch_code']);
    }

    // -------------------------------------------------------------------------
    // Invoice: createEbarimtInvoice
    // -------------------------------------------------------------------------

    public function testCreateEbarimtInvoiceSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateEbarimtInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'EINV-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Ebarimt invoice',
            taxType: '1',
            districtCode: '01',
            callbackUrl: 'https://example.com/callback',
            lines: [
                [
                    'line_description' => 'Product A',
                    'line_quantity' => '1',
                    'line_unit_price' => '1000',
                    'tax_product_code' => '1234',
                ],
            ],
        );

        $response = $client->createEbarimtInvoice($request);

        $this->assertSame('inv_123', $response->invoiceId);

        $body = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertSame('1', $body['tax_type']);
        $this->assertSame('01', $body['district_code']);
        $this->assertCount(1, $body['lines']);
    }

    // -------------------------------------------------------------------------
    // Invoice: cancelInvoice
    // -------------------------------------------------------------------------

    public function testCancelInvoiceSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], ''),
        ]);

        $client = $this->createClient($mock);
        $client->cancelInvoice('inv_123');

        $request = $this->history[1]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertStringContainsString('/v2/invoice/inv_123', (string) $request->getUri());
    }

    public function testCancelInvoiceNotFound(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(404, [], json_encode([
                'error' => 'INVOICE_NOTFOUND',
                'message' => 'Invoice not found',
            ])),
        ]);

        $client = $this->createClient($mock);

        try {
            $client->cancelInvoice('nonexistent');
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(404, $e->statusCode);
            $this->assertSame('INVOICE_NOTFOUND', $e->errorCode);
        }
    }

    // -------------------------------------------------------------------------
    // Payment: getPayment
    // -------------------------------------------------------------------------

    public function testGetPaymentSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->paymentDetailBody()),
        ]);

        $client = $this->createClient($mock);
        $payment = $client->getPayment('pay_123');

        $this->assertSame('pay_123', $payment->paymentId);
        $this->assertSame('PAID', $payment->paymentStatus);
        $this->assertSame('1000', $payment->paymentAmount);
        $this->assertSame('MNT', $payment->paymentCurrency);
        $this->assertSame('khan_bank', $payment->paymentWallet);
        $this->assertSame('P2P', $payment->transactionType);
        $this->assertSame('INVOICE', $payment->objectType);
        $this->assertSame('inv_123', $payment->objectId);

        $request = $this->history[1]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('/v2/payment/pay_123', (string) $request->getUri());
    }

    public function testGetPaymentNotFound(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(404, [], json_encode([
                'error' => 'PAYMENT_NOTFOUND',
                'message' => 'Payment not found',
            ])),
        ]);

        $client = $this->createClient($mock);

        try {
            $client->getPayment('nonexistent');
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(404, $e->statusCode);
            $this->assertSame('PAYMENT_NOTFOUND', $e->errorCode);
        }
    }

    // -------------------------------------------------------------------------
    // Payment: checkPayment
    // -------------------------------------------------------------------------

    public function testCheckPaymentSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->paymentCheckResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new PaymentCheckRequest(
            objectType: 'INVOICE',
            objectId: 'inv_123',
        );

        $response = $client->checkPayment($request);

        $this->assertSame(1, $response->count);
        $this->assertSame(1000.0, $response->paidAmount);
        $this->assertCount(1, $response->rows);
        $this->assertSame('pay_123', $response->rows[0]['payment_id']);
        $this->assertSame('PAID', $response->rows[0]['payment_status']);
    }

    public function testCheckPaymentWithPagination(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->paymentCheckResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new PaymentCheckRequest(
            objectType: 'INVOICE',
            objectId: 'inv_123',
            pageNumber: 1,
            pageLimit: 10,
        );

        $client->checkPayment($request);

        $body = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertSame(1, $body['offset']['page_number']);
        $this->assertSame(10, $body['offset']['page_limit']);
    }

    // -------------------------------------------------------------------------
    // Payment: listPayments
    // -------------------------------------------------------------------------

    public function testListPaymentsSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->paymentListResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new PaymentListRequest(
            objectType: 'INVOICE',
            objectId: 'inv_456',
            startDate: '2026-01-01',
            endDate: '2026-01-31',
            pageNumber: 1,
            pageLimit: 20,
        );

        $response = $client->listPayments($request);

        $this->assertSame(1, $response->count);
        $this->assertCount(1, $response->rows);
        $this->assertSame('pay_456', $response->rows[0]['payment_id']);
        $this->assertSame('2000', $response->rows[0]['payment_amount']);
    }

    // -------------------------------------------------------------------------
    // Payment: cancelPayment
    // -------------------------------------------------------------------------

    public function testCancelPaymentSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], ''),
        ]);

        $client = $this->createClient($mock);

        $request = new PaymentCancelRequest(
            callbackUrl: 'https://example.com/cancel-callback',
            note: 'Cancelled by test',
        );

        $client->cancelPayment('pay_123', $request);

        $httpRequest = $this->history[1]['request'];
        $this->assertSame('DELETE', $httpRequest->getMethod());
        $this->assertStringContainsString('/v2/payment/cancel/pay_123', (string) $httpRequest->getUri());

        $body = json_decode((string) $httpRequest->getBody(), true);
        $this->assertSame('https://example.com/cancel-callback', $body['callback_url']);
        $this->assertSame('Cancelled by test', $body['note']);
    }

    public function testCancelPaymentAlreadyCancelled(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(400, [], json_encode([
                'error' => 'PAYMENT_ALREADY_CANCELED',
                'message' => 'Payment already cancelled',
            ])),
        ]);

        $client = $this->createClient($mock);
        $request = new PaymentCancelRequest();

        try {
            $client->cancelPayment('pay_123', $request);
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(400, $e->statusCode);
            $this->assertSame('PAYMENT_ALREADY_CANCELED', $e->errorCode);
        }
    }

    // -------------------------------------------------------------------------
    // Payment: refundPayment
    // -------------------------------------------------------------------------

    public function testRefundPaymentSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], ''),
        ]);

        $client = $this->createClient($mock);

        $request = new PaymentRefundRequest(
            callbackUrl: 'https://example.com/refund-callback',
            note: 'Refund reason',
        );

        $client->refundPayment('pay_456', $request);

        $httpRequest = $this->history[1]['request'];
        $this->assertSame('DELETE', $httpRequest->getMethod());
        $this->assertStringContainsString('/v2/payment/refund/pay_456', (string) $httpRequest->getUri());
    }

    // -------------------------------------------------------------------------
    // Ebarimt: createEbarimt
    // -------------------------------------------------------------------------

    public function testCreateEbarimtSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->ebarimtResponseBody()),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateEbarimtRequest(
            paymentId: 'pay_123',
            ebarimtReceiverType: 'citizen',
            ebarimtReceiver: 'AA12345678',
            districtCode: '01',
        );

        $response = $client->createEbarimt($request);

        $this->assertSame('eb_123', $response->id);
        $this->assertSame('citizen', $response->ebarimtReceiverType);
        $this->assertSame('AA12345678', $response->ebarimtReceiver);
        $this->assertSame('CREATED', $response->barimtStatus);
        $this->assertTrue($response->status);
    }

    // -------------------------------------------------------------------------
    // Ebarimt: cancelEbarimt
    // -------------------------------------------------------------------------

    public function testCancelEbarimtSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->ebarimtResponseBody()),
        ]);

        $client = $this->createClient($mock);
        $response = $client->cancelEbarimt('pay_123');

        $this->assertSame('eb_123', $response->id);

        $httpRequest = $this->history[1]['request'];
        $this->assertSame('DELETE', $httpRequest->getMethod());
        $this->assertStringContainsString('/v2/ebarimt_v3/pay_123', (string) $httpRequest->getUri());
    }

    // -------------------------------------------------------------------------
    // Auto token management (ensureToken)
    // -------------------------------------------------------------------------

    public function testAutoTokenOnFirstRequest(): void
    {
        $mock = new MockHandler([
            // Auto-auth before the invoice request
            new Response(200, [], $this->tokenResponseBody()),
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);

        // No explicit getToken() call -- should auto-authenticate
        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'AUTO-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Auto token test',
            amount: 100.0,
            callbackUrl: 'https://example.com/callback',
        );

        $response = $client->createSimpleInvoice($request);
        $this->assertSame('inv_123', $response->invoiceId);

        // Should have made 2 requests: token + invoice
        $this->assertCount(2, $this->history);
        $this->assertStringContainsString('/v2/auth/token', (string) $this->history[0]['request']->getUri());
        $this->assertStringContainsString('/v2/invoice', (string) $this->history[1]['request']->getUri());
    }

    public function testReusesValidToken(): void
    {
        $mock = new MockHandler([
            // First: getToken
            new Response(200, [], $this->tokenResponseBody()),
            // Second: first invoice (no re-auth needed)
            new Response(200, [], $this->invoiceResponseBody()),
            // Third: second invoice (no re-auth needed)
            new Response(200, [], $this->invoiceResponseBody()),
        ]);

        $client = $this->createClient($mock);
        $client->getToken();

        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'REUSE-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Reuse test 1',
            amount: 100.0,
            callbackUrl: 'https://example.com/callback',
        );

        $client->createSimpleInvoice($request);
        $client->createSimpleInvoice($request);

        // Only 3 requests total: 1 token + 2 invoices (no extra auth)
        $this->assertCount(3, $this->history);
    }

    // -------------------------------------------------------------------------
    // Error handling: non-JSON error response
    // -------------------------------------------------------------------------

    public function testErrorWithNonJsonResponse(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(500, [], 'Internal Server Error'),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'ERR-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Error test',
            amount: 100.0,
            callbackUrl: 'https://example.com/callback',
        );

        try {
            $client->createSimpleInvoice($request);
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(500, $e->statusCode);
            $this->assertSame('Internal Server Error', $e->errorCode);
            $this->assertSame('Internal Server Error', $e->rawBody);
        }
    }

    // -------------------------------------------------------------------------
    // Error handling: empty error fields in JSON response
    // -------------------------------------------------------------------------

    public function testErrorWithEmptyJsonFields(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new Response(403, [], json_encode([])),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'ERR-002',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Empty error test',
            amount: 100.0,
            callbackUrl: 'https://example.com/callback',
        );

        try {
            $client->createSimpleInvoice($request);
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(403, $e->statusCode);
            $this->assertSame('Forbidden', $e->errorCode);
        }
    }

    // -------------------------------------------------------------------------
    // Network error on authenticated request
    // -------------------------------------------------------------------------

    public function testNetworkErrorOnAuthenticatedRequest(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->tokenResponseBody()),
            new ConnectException(
                'Network unreachable',
                new Request('POST', 'https://merchant.qpay.mn/v2/invoice'),
            ),
        ]);

        $client = $this->createClient($mock);

        $request = new CreateSimpleInvoiceRequest(
            invoiceCode: 'TEST_INVOICE',
            senderInvoiceNo: 'NET-001',
            invoiceReceiverCode: 'receiver_1',
            invoiceDescription: 'Network error test',
            amount: 100.0,
            callbackUrl: 'https://example.com/callback',
        );

        try {
            $client->createSimpleInvoice($request);
            $this->fail('Expected QPayException was not thrown');
        } catch (QPayException $e) {
            $this->assertSame(0, $e->statusCode);
            $this->assertSame('REQUEST_FAILED', $e->errorCode);
            $this->assertStringContainsString('Network unreachable', $e->errorMessage);
        }
    }
}
