<?php

declare(strict_types=1);

namespace QPay\Models;

class PaymentListResponse
{
    public function __construct(
        public readonly int $count,
        public readonly array $rows,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $rows = array_map(
            fn(array $row) => [
                'payment_id' => $row['payment_id'] ?? '',
                'payment_date' => $row['payment_date'] ?? '',
                'payment_status' => $row['payment_status'] ?? '',
                'payment_fee' => $row['payment_fee'] ?? '',
                'payment_amount' => $row['payment_amount'] ?? '',
                'payment_currency' => $row['payment_currency'] ?? '',
                'payment_wallet' => $row['payment_wallet'] ?? '',
                'payment_name' => $row['payment_name'] ?? '',
                'payment_description' => $row['payment_description'] ?? '',
                'qr_code' => $row['qr_code'] ?? '',
                'paid_by' => $row['paid_by'] ?? '',
                'object_type' => $row['object_type'] ?? '',
                'object_id' => $row['object_id'] ?? '',
            ],
            $data['rows'] ?? [],
        );

        return new self(
            count: (int) ($data['count'] ?? 0),
            rows: $rows,
        );
    }
}
