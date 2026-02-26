<?php

declare(strict_types=1);

namespace QPay;

use InvalidArgumentException;

class Config
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $username,
        public readonly string $password,
        public readonly string $invoiceCode,
        public readonly string $callbackUrl,
    ) {
    }

    /**
     * Load configuration from environment variables.
     *
     * Required environment variables:
     * - QPAY_BASE_URL
     * - QPAY_USERNAME
     * - QPAY_PASSWORD
     * - QPAY_INVOICE_CODE
     * - QPAY_CALLBACK_URL
     *
     * @throws InvalidArgumentException if any required variable is missing
     */
    public static function fromEnv(): self
    {
        $required = [
            'QPAY_BASE_URL',
            'QPAY_USERNAME',
            'QPAY_PASSWORD',
            'QPAY_INVOICE_CODE',
            'QPAY_CALLBACK_URL',
        ];

        $values = [];
        foreach ($required as $name) {
            $value = getenv($name);
            if ($value === false || $value === '') {
                throw new InvalidArgumentException(
                    sprintf('Required environment variable %s is not set', $name)
                );
            }
            $values[$name] = $value;
        }

        return new self(
            baseUrl: $values['QPAY_BASE_URL'],
            username: $values['QPAY_USERNAME'],
            password: $values['QPAY_PASSWORD'],
            invoiceCode: $values['QPAY_INVOICE_CODE'],
            callbackUrl: $values['QPAY_CALLBACK_URL'],
        );
    }
}
