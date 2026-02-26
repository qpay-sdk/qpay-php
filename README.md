# QPay PHP SDK

[![Packagist Version](https://img.shields.io/packagist/v/usukhbayar/qpay-php.svg)](https://packagist.org/packages/usukhbayar/qpay-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

PHP SDK for the QPay V2 payment gateway API. Provides a clean, type-safe interface with automatic token management, Guzzle-based HTTP, and comprehensive error handling.

## Requirements

- PHP 8.1+
- Guzzle HTTP 7.0+

## Installation

```bash
composer require usukhbayar/qpay-php
```

## Quick Start

```php
use QPay\Config;
use QPay\QPayClient;
use QPay\Models\CreateSimpleInvoiceRequest;
use QPay\Exceptions\QPayException;

// Configure from environment variables
$config = Config::fromEnv();
$client = new QPayClient($config);

// Or configure manually
$config = new Config(
    baseUrl: 'https://merchant.qpay.mn',
    username: 'YOUR_USERNAME',
    password: 'YOUR_PASSWORD',
    invoiceCode: 'YOUR_INVOICE_CODE',
    callbackUrl: 'https://yoursite.com/callback',
);
$client = new QPayClient($config);

// Create an invoice (token is obtained automatically)
try {
    $invoice = $client->createSimpleInvoice(new CreateSimpleInvoiceRequest(
        invoiceCode: $config->invoiceCode,
        senderInvoiceNo: 'ORDER-001',
        invoiceReceiverCode: 'terminal',
        invoiceDescription: 'Payment for Order #001',
        amount: 10000.0,
        callbackUrl: $config->callbackUrl,
    ));

    echo $invoice->invoiceId;   // Invoice ID
    echo $invoice->qPayShortUrl; // Payment link
    echo $invoice->qrImage;     // QR code image (base64)
} catch (QPayException $e) {
    echo "Error: {$e->errorCode} - {$e->errorMessage}";
}
```

## Configuration

### Environment Variables

| Variable | Description |
|---|---|
| `QPAY_BASE_URL` | QPay API base URL (e.g. `https://merchant.qpay.mn`) |
| `QPAY_USERNAME` | Merchant username |
| `QPAY_PASSWORD` | Merchant password |
| `QPAY_INVOICE_CODE` | Default invoice code |
| `QPAY_CALLBACK_URL` | Payment callback URL |

### Loading from Environment

```php
// All 5 variables must be set, otherwise InvalidArgumentException is thrown
$config = Config::fromEnv();
```

### Manual Configuration

```php
$config = new Config(
    baseUrl: 'https://merchant.qpay.mn',
    username: 'YOUR_USERNAME',
    password: 'YOUR_PASSWORD',
    invoiceCode: 'YOUR_INVOICE_CODE',
    callbackUrl: 'https://yoursite.com/callback',
);
```

## Token Management

The SDK handles authentication automatically. When you make any API call, the client will:

1. Obtain an access token if none exists
2. Reuse the token while it is valid
3. Refresh the token using the refresh token when expired
4. Re-authenticate with credentials if the refresh token is also expired

You can also manage tokens manually:

```php
// Explicitly obtain a token
$token = $client->getToken();
echo $token->accessToken;
echo $token->refreshToken;
echo $token->expiresIn;

// Explicitly refresh the token
$refreshed = $client->refreshTokenRequest();
```

## Usage

### Create Invoice (Full Options)

```php
use QPay\Models\CreateInvoiceRequest;

$invoice = $client->createInvoice(new CreateInvoiceRequest(
    invoiceCode: 'YOUR_INVOICE_CODE',
    senderInvoiceNo: 'ORDER-001',
    invoiceReceiverCode: 'terminal',
    invoiceDescription: 'Payment for services',
    amount: 50000.0,
    callbackUrl: 'https://yoursite.com/callback',
    // Optional fields:
    senderBranchCode: 'BRANCH_01',
    allowPartial: true,
    minimumAmount: 10000.0,
    allowExceed: false,
    maximumAmount: 100000.0,
    note: 'Additional notes',
    lines: [
        [
            'line_description' => 'Product A',
            'line_quantity' => '2',
            'line_unit_price' => '25000',
        ],
    ],
));

echo $invoice->invoiceId;
echo $invoice->qrText;
echo $invoice->qrImage;
echo $invoice->qPayShortUrl;

foreach ($invoice->urls as $url) {
    echo "{$url['name']}: {$url['link']}";
}
```

### Create Simple Invoice

```php
use QPay\Models\CreateSimpleInvoiceRequest;

$invoice = $client->createSimpleInvoice(new CreateSimpleInvoiceRequest(
    invoiceCode: 'YOUR_INVOICE_CODE',
    senderInvoiceNo: 'ORDER-002',
    invoiceReceiverCode: 'terminal',
    invoiceDescription: 'Simple payment',
    amount: 5000.0,
    callbackUrl: 'https://yoursite.com/callback',
    senderBranchCode: 'BRANCH_01', // optional
));
```

### Create Invoice with Ebarimt (Tax)

```php
use QPay\Models\CreateEbarimtInvoiceRequest;

$invoice = $client->createEbarimtInvoice(new CreateEbarimtInvoiceRequest(
    invoiceCode: 'YOUR_INVOICE_CODE',
    senderInvoiceNo: 'ORDER-003',
    invoiceReceiverCode: 'terminal',
    invoiceDescription: 'Invoice with tax receipt',
    taxType: '1',
    districtCode: '01',
    callbackUrl: 'https://yoursite.com/callback',
    lines: [
        [
            'line_description' => 'Product B',
            'line_quantity' => '1',
            'line_unit_price' => '30000',
            'tax_product_code' => '1234',
        ],
    ],
));
```

### Cancel Invoice

```php
$client->cancelInvoice('INVOICE_ID');
```

### Get Payment Details

```php
$payment = $client->getPayment('PAYMENT_ID');

echo $payment->paymentId;
echo $payment->paymentStatus;   // e.g. "PAID"
echo $payment->paymentAmount;
echo $payment->paymentCurrency; // e.g. "MNT"
echo $payment->paymentWallet;
echo $payment->transactionType;
echo $payment->paymentDate;
```

### Check Payment Status

```php
use QPay\Models\PaymentCheckRequest;

$result = $client->checkPayment(new PaymentCheckRequest(
    objectType: 'INVOICE',
    objectId: 'INVOICE_ID',
    pageNumber: 1,  // optional
    pageLimit: 10,   // optional
));

echo $result->count;
echo $result->paidAmount;

foreach ($result->rows as $row) {
    echo "{$row['payment_id']}: {$row['payment_status']} ({$row['payment_amount']})";
}
```

### List Payments

```php
use QPay\Models\PaymentListRequest;

$result = $client->listPayments(new PaymentListRequest(
    objectType: 'INVOICE',
    objectId: 'INVOICE_ID',
    startDate: '2026-01-01',
    endDate: '2026-01-31',
    pageNumber: 1,
    pageLimit: 20,
));

echo $result->count;

foreach ($result->rows as $row) {
    echo "{$row['payment_id']}: {$row['payment_amount']} {$row['payment_currency']}";
}
```

### Cancel Payment (Card Only)

```php
use QPay\Models\PaymentCancelRequest;

$client->cancelPayment('PAYMENT_ID', new PaymentCancelRequest(
    callbackUrl: 'https://yoursite.com/cancel-callback', // optional
    note: 'Reason for cancellation',                      // optional
));
```

### Refund Payment (Card Only)

```php
use QPay\Models\PaymentRefundRequest;

$client->refundPayment('PAYMENT_ID', new PaymentRefundRequest(
    callbackUrl: 'https://yoursite.com/refund-callback', // optional
    note: 'Reason for refund',                            // optional
));
```

### Create Ebarimt (Tax Receipt)

```php
use QPay\Models\CreateEbarimtRequest;

$ebarimt = $client->createEbarimt(new CreateEbarimtRequest(
    paymentId: 'PAYMENT_ID',
    ebarimtReceiverType: 'citizen',
    ebarimtReceiver: 'AA12345678',    // optional
    districtCode: '01',                // optional
    classificationCode: '1234',        // optional
));

echo $ebarimt->id;
echo $ebarimt->barimtStatus;
echo $ebarimt->ebarimtQrData;
echo $ebarimt->ebarimtLottery;
echo $ebarimt->amount;
echo $ebarimt->vatAmount;
```

### Cancel Ebarimt

```php
$ebarimt = $client->cancelEbarimt('PAYMENT_ID');

echo $ebarimt->barimtStatus;
```

## Error Handling

All API errors throw `QPay\Exceptions\QPayException`:

```php
use QPay\Exceptions\QPayException;

try {
    $client->createSimpleInvoice($request);
} catch (QPayException $e) {
    echo $e->statusCode;    // HTTP status code (0 for network errors)
    echo $e->errorCode;     // QPay error code string
    echo $e->errorMessage;  // Human-readable error message
    echo $e->rawBody;       // Raw response body
    echo $e->getMessage();  // "qpay: ERROR_CODE - message (status 400)"
    echo $e->getCode();     // Same as statusCode
}
```

### Error Code Constants

The `QPayException` class provides constants for all known QPay error codes:

```php
use QPay\Exceptions\QPayException;

try {
    $client->cancelInvoice('inv_123');
} catch (QPayException $e) {
    match ($e->errorCode) {
        QPayException::INVOICE_NOTFOUND => handleNotFound(),
        QPayException::INVOICE_ALREADY_CANCELED => handleAlreadyCancelled(),
        QPayException::INVOICE_PAID => handleAlreadyPaid(),
        QPayException::AUTHENTICATION_FAILED => handleAuthError(),
        QPayException::PERMISSION_DENIED => handlePermissionDenied(),
        default => handleUnknownError($e),
    };
}
```

Available error code constants:

| Constant | Value |
|---|---|
| `AUTHENTICATION_FAILED` | Authentication failed |
| `PERMISSION_DENIED` | Permission denied |
| `NO_CREDENDIALS` | Missing credentials |
| `INVOICE_NOTFOUND` | Invoice not found |
| `INVOICE_PAID` | Invoice already paid |
| `INVOICE_ALREADY_CANCELED` | Invoice already cancelled |
| `INVOICE_CODE_INVALID` | Invalid invoice code |
| `INVOICE_LINE_REQUIRED` | Invoice lines required |
| `PAYMENT_NOTFOUND` | Payment not found |
| `PAYMENT_NOT_PAID` | Payment not paid |
| `PAYMENT_ALREADY_CANCELED` | Payment already cancelled |
| `INVALID_AMOUNT` | Invalid amount |
| `MIN_AMOUNT_ERR` | Below minimum amount |
| `MAX_AMOUNT_ERR` | Exceeds maximum amount |
| `MERCHANT_NOTFOUND` | Merchant not found |
| `MERCHANT_INACTIVE` | Merchant inactive |
| `EBARIMT_NOT_REGISTERED` | Ebarimt not registered |
| `EBARIMT_CANCEL_NOTSUPPERDED` | Ebarimt cancel not supported |
| `CUSTOMER_NOTFOUND` | Customer not found |
| `CUSTOMER_DUPLICATE` | Duplicate customer |

See the `QPayException` class for the full list of constants.

## API Reference

### QPayClient Methods

| Method | Parameters | Returns | Description |
|---|---|---|---|
| `getToken()` | - | `TokenResponse` | Authenticate and get token pair |
| `refreshTokenRequest()` | - | `TokenResponse` | Refresh the access token |
| `createInvoice()` | `CreateInvoiceRequest` | `InvoiceResponse` | Create invoice with full options |
| `createSimpleInvoice()` | `CreateSimpleInvoiceRequest` | `InvoiceResponse` | Create invoice with minimal fields |
| `createEbarimtInvoice()` | `CreateEbarimtInvoiceRequest` | `InvoiceResponse` | Create invoice with tax info |
| `cancelInvoice()` | `string $invoiceId` | `void` | Cancel an invoice |
| `getPayment()` | `string $paymentId` | `PaymentDetail` | Get payment details |
| `checkPayment()` | `PaymentCheckRequest` | `PaymentCheckResponse` | Check payment status |
| `listPayments()` | `PaymentListRequest` | `PaymentListResponse` | List payments |
| `cancelPayment()` | `string $paymentId, PaymentCancelRequest` | `void` | Cancel a card payment |
| `refundPayment()` | `string $paymentId, PaymentRefundRequest` | `void` | Refund a card payment |
| `createEbarimt()` | `CreateEbarimtRequest` | `EbarimtResponse` | Create tax receipt |
| `cancelEbarimt()` | `string $paymentId` | `EbarimtResponse` | Cancel tax receipt |

### Custom HTTP Client

You can inject a custom Guzzle client (useful for testing, proxies, or custom middleware):

```php
use GuzzleHttp\Client as HttpClient;

$http = new HttpClient([
    'timeout' => 60,
    'proxy' => 'tcp://localhost:8080',
]);

$client = new QPayClient($config, $http);
```

## Testing

```bash
composer install
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.
