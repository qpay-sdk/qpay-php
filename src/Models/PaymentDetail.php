<?php

declare(strict_types=1);

namespace QPay\Models;

class PaymentDetail
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $paymentStatus,
        public readonly string $paymentFee,
        public readonly string $paymentAmount,
        public readonly string $paymentCurrency,
        public readonly string $paymentDate,
        public readonly string $paymentWallet,
        public readonly string $transactionType,
        public readonly string $objectType,
        public readonly string $objectId,
        public readonly ?string $nextPaymentDate,
        public readonly ?string $nextPaymentDatetime,
        public readonly array $cardTransactions,
        public readonly array $p2pTransactions,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: $data['payment_id'] ?? '',
            paymentStatus: $data['payment_status'] ?? '',
            paymentFee: $data['payment_fee'] ?? '',
            paymentAmount: $data['payment_amount'] ?? '',
            paymentCurrency: $data['payment_currency'] ?? '',
            paymentDate: $data['payment_date'] ?? '',
            paymentWallet: $data['payment_wallet'] ?? '',
            transactionType: $data['transaction_type'] ?? '',
            objectType: $data['object_type'] ?? '',
            objectId: $data['object_id'] ?? '',
            nextPaymentDate: $data['next_payment_date'] ?? null,
            nextPaymentDatetime: $data['next_payment_datetime'] ?? null,
            cardTransactions: $data['card_transactions'] ?? [],
            p2pTransactions: $data['p2p_transactions'] ?? [],
        );
    }
}
