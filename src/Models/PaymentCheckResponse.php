<?php

declare(strict_types=1);

namespace QPay\Models;

class PaymentCheckResponse
{
    public function __construct(
        public readonly int $count,
        public readonly float $paidAmount,
        public readonly array $rows,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $rows = array_map(
            fn(array $row) => [
                'payment_id' => $row['payment_id'] ?? '',
                'payment_status' => $row['payment_status'] ?? '',
                'payment_amount' => $row['payment_amount'] ?? '',
                'trx_fee' => $row['trx_fee'] ?? '',
                'payment_currency' => $row['payment_currency'] ?? '',
                'payment_wallet' => $row['payment_wallet'] ?? '',
                'payment_type' => $row['payment_type'] ?? '',
                'next_payment_date' => $row['next_payment_date'] ?? null,
                'next_payment_datetime' => $row['next_payment_datetime'] ?? null,
                'card_transactions' => $row['card_transactions'] ?? [],
                'p2p_transactions' => $row['p2p_transactions'] ?? [],
            ],
            $data['rows'] ?? [],
        );

        return new self(
            count: (int) ($data['count'] ?? 0),
            paidAmount: (float) ($data['paid_amount'] ?? 0),
            rows: $rows,
        );
    }
}
