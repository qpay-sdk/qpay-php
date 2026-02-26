<?php

declare(strict_types=1);

namespace QPay\Exceptions;

use Exception;

class QPayException extends Exception
{
    // --- Error code constants ---
    public const ACCOUNT_BANK_DUPLICATED = 'ACCOUNT_BANK_DUPLICATED';
    public const ACCOUNT_SELECTION_INVALID = 'ACCOUNT_SELECTION_INVALID';
    public const AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
    public const BANK_ACCOUNT_NOTFOUND = 'BANK_ACCOUNT_NOTFOUND';
    public const BANK_MCC_ALREADY_ADDED = 'BANK_MCC_ALREADY_ADDED';
    public const BANK_MCC_NOT_FOUND = 'BANK_MCC_NOT_FOUND';
    public const CARD_TERMINAL_NOTFOUND = 'CARD_TERMINAL_NOTFOUND';
    public const CLIENT_NOTFOUND = 'CLIENT_NOTFOUND';
    public const CLIENT_USERNAME_DUPLICATED = 'CLIENT_USERNAME_DUPLICATED';
    public const CUSTOMER_DUPLICATE = 'CUSTOMER_DUPLICATE';
    public const CUSTOMER_NOTFOUND = 'CUSTOMER_NOTFOUND';
    public const CUSTOMER_REGISTER_INVALID = 'CUSTOMER_REGISTER_INVALID';
    public const EBARIMT_CANCEL_NOTSUPPERDED = 'EBARIMT_CANCEL_NOTSUPPERDED';
    public const EBARIMT_NOT_REGISTERED = 'EBARIMT_NOT_REGISTERED';
    public const EBARIMT_QR_CODE_INVALID = 'EBARIMT_QR_CODE_INVALID';
    public const INFORM_NOTFOUND = 'INFORM_NOTFOUND';
    public const INPUT_CODE_REGISTERED = 'INPUT_CODE_REGISTERED';
    public const INPUT_NOTFOUND = 'INPUT_NOTFOUND';
    public const INVALID_AMOUNT = 'INVALID_AMOUNT';
    public const INVALID_OBJECT_TYPE = 'INVALID_OBJECT_TYPE';
    public const INVOICE_ALREADY_CANCELED = 'INVOICE_ALREADY_CANCELED';
    public const INVOICE_CODE_INVALID = 'INVOICE_CODE_INVALID';
    public const INVOICE_CODE_REGISTERED = 'INVOICE_CODE_REGISTERED';
    public const INVOICE_LINE_REQUIRED = 'INVOICE_LINE_REQUIRED';
    public const INVOICE_NOTFOUND = 'INVOICE_NOTFOUND';
    public const INVOICE_PAID = 'INVOICE_PAID';
    public const INVOICE_RECEIVER_DATA_ADDRESS_REQUIRED = 'INVOICE_RECEIVER_DATA_ADDRESS_REQUIRED';
    public const INVOICE_RECEIVER_DATA_EMAIL_REQUIRED = 'INVOICE_RECEIVER_DATA_EMAIL_REQUIRED';
    public const INVOICE_RECEIVER_DATA_PHONE_REQUIRED = 'INVOICE_RECEIVER_DATA_PHONE_REQUIRED';
    public const INVOICE_RECEIVER_DATA_REQUIRED = 'INVOICE_RECEIVER_DATA_REQUIRED';
    public const MAX_AMOUNT_ERR = 'MAX_AMOUNT_ERR';
    public const MCC_NOTFOUND = 'MCC_NOTFOUND';
    public const MERCHANT_ALREADY_REGISTERED = 'MERCHANT_ALREADY_REGISTERED';
    public const MERCHANT_INACTIVE = 'MERCHANT_INACTIVE';
    public const MERCHANT_NOTFOUND = 'MERCHANT_NOTFOUND';
    public const MIN_AMOUNT_ERR = 'MIN_AMOUNT_ERR';
    public const NO_CREDENDIALS = 'NO_CREDENDIALS';
    public const OBJECT_DATA_ERROR = 'OBJECT_DATA_ERROR';
    public const P2P_TERMINAL_NOTFOUND = 'P2P_TERMINAL_NOTFOUND';
    public const PAYMENT_ALREADY_CANCELED = 'PAYMENT_ALREADY_CANCELED';
    public const PAYMENT_NOT_PAID = 'PAYMENT_NOT_PAID';
    public const PAYMENT_NOTFOUND = 'PAYMENT_NOTFOUND';
    public const PERMISSION_DENIED = 'PERMISSION_DENIED';
    public const QRACCOUNT_INACTIVE = 'QRACCOUNT_INACTIVE';
    public const QRACCOUNT_NOTFOUND = 'QRACCOUNT_NOTFOUND';
    public const QRCODE_NOTFOUND = 'QRCODE_NOTFOUND';
    public const QRCODE_USED = 'QRCODE_USED';
    public const SENDER_BRANCH_DATA_REQUIRED = 'SENDER_BRANCH_DATA_REQUIRED';
    public const TAX_LINE_REQUIRED = 'TAX_LINE_REQUIRED';
    public const TAX_PRODUCT_CODE_REQUIRED = 'TAX_PRODUCT_CODE_REQUIRED';
    public const TRANSACTION_NOT_APPROVED = 'TRANSACTION_NOT_APPROVED';
    public const TRANSACTION_REQUIRED = 'TRANSACTION_REQUIRED';

    public function __construct(
        public readonly int $statusCode,
        public readonly string $errorCode,
        public readonly string $errorMessage,
        public readonly string $rawBody = '',
    ) {
        parent::__construct(
            sprintf('qpay: %s - %s (status %d)', $this->errorCode, $this->errorMessage, $this->statusCode),
            $this->statusCode,
        );
    }
}
