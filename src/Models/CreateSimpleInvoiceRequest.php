<?php

declare(strict_types=1);

namespace QPay\Models;

class CreateSimpleInvoiceRequest
{
    public function __construct(
        public readonly string $invoiceCode,
        public readonly string $senderInvoiceNo,
        public readonly string $invoiceReceiverCode,
        public readonly string $invoiceDescription,
        public readonly float $amount,
        public readonly string $callbackUrl,
        public readonly ?string $senderBranchCode = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'invoice_code' => $this->invoiceCode,
            'sender_invoice_no' => $this->senderInvoiceNo,
            'invoice_receiver_code' => $this->invoiceReceiverCode,
            'invoice_description' => $this->invoiceDescription,
            'amount' => $this->amount,
            'callback_url' => $this->callbackUrl,
        ];

        if ($this->senderBranchCode !== null) {
            $data['sender_branch_code'] = $this->senderBranchCode;
        }

        return $data;
    }
}
