<?php

declare(strict_types=1);

namespace QPay\Tests;

use PHPUnit\Framework\TestCase;
use QPay\Exceptions\QPayException;

class QPayExceptionTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Basic creation
    // -------------------------------------------------------------------------

    public function testCreation(): void
    {
        $exception = new QPayException(
            statusCode: 400,
            errorCode: 'INVOICE_CODE_INVALID',
            errorMessage: 'Invoice code is invalid',
        );

        $this->assertSame(400, $exception->statusCode);
        $this->assertSame('INVOICE_CODE_INVALID', $exception->errorCode);
        $this->assertSame('Invoice code is invalid', $exception->errorMessage);
        $this->assertSame('', $exception->rawBody);
    }

    // -------------------------------------------------------------------------
    // Creation with raw body
    // -------------------------------------------------------------------------

    public function testCreationWithRawBody(): void
    {
        $rawBody = '{"error":"INVOICE_NOTFOUND","message":"Not found"}';

        $exception = new QPayException(
            statusCode: 404,
            errorCode: 'INVOICE_NOTFOUND',
            errorMessage: 'Not found',
            rawBody: $rawBody,
        );

        $this->assertSame(404, $exception->statusCode);
        $this->assertSame('INVOICE_NOTFOUND', $exception->errorCode);
        $this->assertSame('Not found', $exception->errorMessage);
        $this->assertSame($rawBody, $exception->rawBody);
    }

    // -------------------------------------------------------------------------
    // Message format
    // -------------------------------------------------------------------------

    public function testMessageFormat(): void
    {
        $exception = new QPayException(
            statusCode: 401,
            errorCode: 'AUTHENTICATION_FAILED',
            errorMessage: 'Invalid credentials',
        );

        $this->assertSame(
            'qpay: AUTHENTICATION_FAILED - Invalid credentials (status 401)',
            $exception->getMessage()
        );
    }

    // -------------------------------------------------------------------------
    // Exception code is set to HTTP status code
    // -------------------------------------------------------------------------

    public function testExceptionCodeIsStatusCode(): void
    {
        $exception = new QPayException(
            statusCode: 500,
            errorCode: 'Internal Server Error',
            errorMessage: 'Server error',
        );

        $this->assertSame(500, $exception->getCode());
    }

    // -------------------------------------------------------------------------
    // Is instance of Exception
    // -------------------------------------------------------------------------

    public function testIsInstanceOfException(): void
    {
        $exception = new QPayException(
            statusCode: 400,
            errorCode: 'ERROR',
            errorMessage: 'test',
        );

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    // -------------------------------------------------------------------------
    // Can be caught as Exception
    // -------------------------------------------------------------------------

    public function testCanBeCaughtAsException(): void
    {
        $caught = false;

        try {
            throw new QPayException(
                statusCode: 422,
                errorCode: 'INVALID_AMOUNT',
                errorMessage: 'Amount must be positive',
            );
        } catch (\Exception $e) {
            $caught = true;
            $this->assertInstanceOf(QPayException::class, $e);
        }

        $this->assertTrue($caught);
    }

    // -------------------------------------------------------------------------
    // Error code constants
    // -------------------------------------------------------------------------

    public function testErrorCodeConstants(): void
    {
        $this->assertSame('AUTHENTICATION_FAILED', QPayException::AUTHENTICATION_FAILED);
        $this->assertSame('INVOICE_NOTFOUND', QPayException::INVOICE_NOTFOUND);
        $this->assertSame('INVOICE_PAID', QPayException::INVOICE_PAID);
        $this->assertSame('INVOICE_ALREADY_CANCELED', QPayException::INVOICE_ALREADY_CANCELED);
        $this->assertSame('INVOICE_CODE_INVALID', QPayException::INVOICE_CODE_INVALID);
        $this->assertSame('INVOICE_LINE_REQUIRED', QPayException::INVOICE_LINE_REQUIRED);
        $this->assertSame('PAYMENT_NOTFOUND', QPayException::PAYMENT_NOTFOUND);
        $this->assertSame('PAYMENT_NOT_PAID', QPayException::PAYMENT_NOT_PAID);
        $this->assertSame('PAYMENT_ALREADY_CANCELED', QPayException::PAYMENT_ALREADY_CANCELED);
        $this->assertSame('INVALID_AMOUNT', QPayException::INVALID_AMOUNT);
        $this->assertSame('PERMISSION_DENIED', QPayException::PERMISSION_DENIED);
        $this->assertSame('MERCHANT_NOTFOUND', QPayException::MERCHANT_NOTFOUND);
        $this->assertSame('MERCHANT_INACTIVE', QPayException::MERCHANT_INACTIVE);
        $this->assertSame('NO_CREDENDIALS', QPayException::NO_CREDENDIALS);
        $this->assertSame('EBARIMT_NOT_REGISTERED', QPayException::EBARIMT_NOT_REGISTERED);
        $this->assertSame('EBARIMT_CANCEL_NOTSUPPERDED', QPayException::EBARIMT_CANCEL_NOTSUPPERDED);
        $this->assertSame('EBARIMT_QR_CODE_INVALID', QPayException::EBARIMT_QR_CODE_INVALID);
        $this->assertSame('CUSTOMER_NOTFOUND', QPayException::CUSTOMER_NOTFOUND);
        $this->assertSame('CUSTOMER_DUPLICATE', QPayException::CUSTOMER_DUPLICATE);
        $this->assertSame('CLIENT_NOTFOUND', QPayException::CLIENT_NOTFOUND);
        $this->assertSame('QRACCOUNT_NOTFOUND', QPayException::QRACCOUNT_NOTFOUND);
        $this->assertSame('QRACCOUNT_INACTIVE', QPayException::QRACCOUNT_INACTIVE);
        $this->assertSame('MIN_AMOUNT_ERR', QPayException::MIN_AMOUNT_ERR);
        $this->assertSame('MAX_AMOUNT_ERR', QPayException::MAX_AMOUNT_ERR);
    }

    // -------------------------------------------------------------------------
    // Using constants in matching
    // -------------------------------------------------------------------------

    public function testErrorCodeMatchingWithConstants(): void
    {
        $exception = new QPayException(
            statusCode: 404,
            errorCode: 'INVOICE_NOTFOUND',
            errorMessage: 'Invoice not found',
        );

        $this->assertSame(QPayException::INVOICE_NOTFOUND, $exception->errorCode);
    }

    // -------------------------------------------------------------------------
    // Zero status code (network errors)
    // -------------------------------------------------------------------------

    public function testZeroStatusCodeForNetworkError(): void
    {
        $exception = new QPayException(
            statusCode: 0,
            errorCode: 'REQUEST_FAILED',
            errorMessage: 'Connection timed out',
        );

        $this->assertSame(0, $exception->statusCode);
        $this->assertSame(0, $exception->getCode());
        $this->assertSame('REQUEST_FAILED', $exception->errorCode);
        $this->assertSame(
            'qpay: REQUEST_FAILED - Connection timed out (status 0)',
            $exception->getMessage()
        );
    }

    // -------------------------------------------------------------------------
    // Readonly properties
    // -------------------------------------------------------------------------

    public function testPropertiesAreReadonly(): void
    {
        $exception = new QPayException(
            statusCode: 400,
            errorCode: 'ERROR',
            errorMessage: 'test',
            rawBody: 'body',
        );

        $reflection = new \ReflectionClass($exception);

        $readonlyProps = ['statusCode', 'errorCode', 'errorMessage', 'rawBody'];
        foreach ($readonlyProps as $propName) {
            $property = $reflection->getProperty($propName);
            $this->assertTrue(
                $property->isReadOnly(),
                "Property {$propName} should be readonly"
            );
        }
    }
}
