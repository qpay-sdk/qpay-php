<?php

declare(strict_types=1);

namespace QPay\Models;

class CreateInvoiceRequest
{
    public function __construct(
        public readonly string $invoiceCode,
        public readonly string $senderInvoiceNo,
        public readonly string $invoiceReceiverCode,
        public readonly string $invoiceDescription,
        public readonly float $amount,
        public readonly string $callbackUrl,
        public readonly ?string $senderBranchCode = null,
        public readonly ?array $senderBranchData = null,
        public readonly ?array $senderStaffData = null,
        public readonly ?string $senderStaffCode = null,
        public readonly ?array $invoiceReceiverData = null,
        public readonly ?string $enableExpiry = null,
        public readonly ?bool $allowPartial = null,
        public readonly ?float $minimumAmount = null,
        public readonly ?bool $allowExceed = null,
        public readonly ?float $maximumAmount = null,
        public readonly ?string $senderTerminalCode = null,
        public readonly mixed $senderTerminalData = null,
        public readonly ?bool $allowSubscribe = null,
        public readonly ?string $subscriptionInterval = null,
        public readonly ?string $subscriptionWebhook = null,
        public readonly ?string $note = null,
        public readonly ?array $transactions = null,
        public readonly ?array $lines = null,
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
        if ($this->senderBranchData !== null) {
            $data['sender_branch_data'] = $this->senderBranchData;
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
        if ($this->enableExpiry !== null) {
            $data['enable_expiry'] = $this->enableExpiry;
        }
        if ($this->allowPartial !== null) {
            $data['allow_partial'] = $this->allowPartial;
        }
        if ($this->minimumAmount !== null) {
            $data['minimum_amount'] = $this->minimumAmount;
        }
        if ($this->allowExceed !== null) {
            $data['allow_exceed'] = $this->allowExceed;
        }
        if ($this->maximumAmount !== null) {
            $data['maximum_amount'] = $this->maximumAmount;
        }
        if ($this->senderTerminalCode !== null) {
            $data['sender_terminal_code'] = $this->senderTerminalCode;
        }
        if ($this->senderTerminalData !== null) {
            $data['sender_terminal_data'] = $this->senderTerminalData;
        }
        if ($this->allowSubscribe !== null) {
            $data['allow_subscribe'] = $this->allowSubscribe;
        }
        if ($this->subscriptionInterval !== null) {
            $data['subscription_interval'] = $this->subscriptionInterval;
        }
        if ($this->subscriptionWebhook !== null) {
            $data['subscription_webhook'] = $this->subscriptionWebhook;
        }
        if ($this->note !== null) {
            $data['note'] = $this->note;
        }
        if ($this->transactions !== null) {
            $data['transactions'] = $this->transactions;
        }
        if ($this->lines !== null) {
            $data['lines'] = $this->lines;
        }

        return $data;
    }
}
