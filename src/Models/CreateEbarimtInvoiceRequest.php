<?php

declare(strict_types=1);

namespace QPay\Models;

class CreateEbarimtInvoiceRequest
{
    public function __construct(
        public readonly string $invoiceCode,
        public readonly string $senderInvoiceNo,
        public readonly string $invoiceReceiverCode,
        public readonly string $invoiceDescription,
        public readonly string $taxType,
        public readonly string $districtCode,
        public readonly string $callbackUrl,
        public readonly array $lines,
        public readonly ?string $senderBranchCode = null,
        public readonly ?array $senderStaffData = null,
        public readonly ?string $senderStaffCode = null,
        public readonly ?array $invoiceReceiverData = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'invoice_code' => $this->invoiceCode,
            'sender_invoice_no' => $this->senderInvoiceNo,
            'invoice_receiver_code' => $this->invoiceReceiverCode,
            'invoice_description' => $this->invoiceDescription,
            'tax_type' => $this->taxType,
            'district_code' => $this->districtCode,
            'callback_url' => $this->callbackUrl,
            'lines' => $this->lines,
        ];

        if ($this->senderBranchCode !== null) {
            $data['sender_branch_code'] = $this->senderBranchCode;
        }
        if ($this->senderStaffData !== null) {
            $data['sender_staff_data'] = $this->senderStaffData;
        }
        if ($this->senderStaffCode !== null) {
            $data['sender_staff_code'] = $this->senderStaffCode;
        }
        if ($this->invoiceReceiverData !== null) {
            $data['invoice_receiver_data'] = $this->invoiceReceiverData;
        }

        return $data;
    }
}
