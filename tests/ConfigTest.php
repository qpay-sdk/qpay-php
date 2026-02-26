<?php

declare(strict_types=1);

namespace QPay\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QPay\Config;

class ConfigTest extends TestCase
{
    private array $envVars = [
        'QPAY_BASE_URL' => 'https://merchant.qpay.mn',
        'QPAY_USERNAME' => 'test_user',
        'QPAY_PASSWORD' => 'test_pass',
        'QPAY_INVOICE_CODE' => 'TEST_CODE',
        'QPAY_CALLBACK_URL' => 'https://example.com/callback',
    ];

    protected function setUp(): void
    {
        // Set all env vars before each test
        foreach ($this->envVars as $key => $value) {
            putenv("{$key}={$value}");
        }
    }

    protected function tearDown(): void
    {
        // Clear all env vars after each test
        foreach (array_keys($this->envVars) as $key) {
            putenv($key);
        }
    }

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function testConstructor(): void
    {
        $config = new Config(
            baseUrl: 'https://merchant.qpay.mn',
            username: 'user',
            password: 'pass',
            invoiceCode: 'CODE',
            callbackUrl: 'https://example.com/cb',
        );

        $this->assertSame('https://merchant.qpay.mn', $config->baseUrl);
        $this->assertSame('user', $config->username);
        $this->assertSame('pass', $config->password);
        $this->assertSame('CODE', $config->invoiceCode);
        $this->assertSame('https://example.com/cb', $config->callbackUrl);
    }

    // -------------------------------------------------------------------------
    // fromEnv: success
    // -------------------------------------------------------------------------

    public function testFromEnvSuccess(): void
    {
        $config = Config::fromEnv();

        $this->assertSame('https://merchant.qpay.mn', $config->baseUrl);
        $this->assertSame('test_user', $config->username);
        $this->assertSame('test_pass', $config->password);
        $this->assertSame('TEST_CODE', $config->invoiceCode);
        $this->assertSame('https://example.com/callback', $config->callbackUrl);
    }

    // -------------------------------------------------------------------------
    // fromEnv: missing individual variables
    // -------------------------------------------------------------------------

    public function testFromEnvMissingBaseUrl(): void
    {
        putenv('QPAY_BASE_URL');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_BASE_URL');

        Config::fromEnv();
    }

    public function testFromEnvMissingUsername(): void
    {
        putenv('QPAY_USERNAME');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_USERNAME');

        Config::fromEnv();
    }

    public function testFromEnvMissingPassword(): void
    {
        putenv('QPAY_PASSWORD');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_PASSWORD');

        Config::fromEnv();
    }

    public function testFromEnvMissingInvoiceCode(): void
    {
        putenv('QPAY_INVOICE_CODE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_INVOICE_CODE');

        Config::fromEnv();
    }

    public function testFromEnvMissingCallbackUrl(): void
    {
        putenv('QPAY_CALLBACK_URL');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_CALLBACK_URL');

        Config::fromEnv();
    }

    // -------------------------------------------------------------------------
    // fromEnv: empty string treated as missing
    // -------------------------------------------------------------------------

    public function testFromEnvEmptyStringTreatedAsMissing(): void
    {
        putenv('QPAY_BASE_URL=');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_BASE_URL');

        Config::fromEnv();
    }

    // -------------------------------------------------------------------------
    // fromEnv: all variables missing
    // -------------------------------------------------------------------------

    public function testFromEnvAllMissing(): void
    {
        foreach (array_keys($this->envVars) as $key) {
            putenv($key);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('QPAY_BASE_URL');

        Config::fromEnv();
    }

    // -------------------------------------------------------------------------
    // Readonly properties
    // -------------------------------------------------------------------------

    public function testPropertiesAreReadonly(): void
    {
        $config = new Config(
            baseUrl: 'https://merchant.qpay.mn',
            username: 'user',
            password: 'pass',
            invoiceCode: 'CODE',
            callbackUrl: 'https://example.com/cb',
        );

        $reflection = new \ReflectionClass($config);

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue(
                $property->isReadOnly(),
                "Property {$property->getName()} should be readonly"
            );
        }
    }
}
