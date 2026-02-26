<?php

declare(strict_types=1);

namespace QPay\Models;

class EbarimtResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $ebarimtBy,
        public readonly string $gWalletId,
        public readonly string $gWalletCustomerId,
        public readonly string $ebarimtReceiverType,
        public readonly string $ebarimtReceiver,
        public readonly string $ebarimtDistrictCode,
        public readonly string $ebarimtBillType,
        public readonly string $gMerchantId,
        public readonly string $merchantBranchCode,
        public readonly ?string $merchantTerminalCode,
        public readonly ?string $merchantStaffCode,
        public readonly string $merchantRegisterNo,
        public readonly string $gPaymentId,
        public readonly string $paidBy,
        public readonly string $objectType,
        public readonly string $objectId,
        public readonly string $amount,
        public readonly string $vatAmount,
        public readonly string $cityTaxAmount,
        public readonly string $ebarimtQrData,
        public readonly string $ebarimtLottery,
        public readonly ?string $note,
        public readonly string $barimtStatus,
        public readonly string $barimtStatusDate,
        public readonly ?string $ebarimtSentEmail,
        public readonly string $ebarimtReceiverPhone,
        public readonly string $taxType,
        public readonly string $merchantTin,
        public readonly string $ebarimtReceiptId,
        public readonly string $createdBy,
        public readonly string $createdDate,
        public readonly string $updatedBy,
        public readonly string $updatedDate,
        public readonly bool $status,
        public readonly array $barimtItems,
        public readonly array $barimtTransactions,
        public readonly array $barimtHistories,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            ebarimtBy: $data['ebarimt_by'] ?? '',
            gWalletId: $data['g_wallet_id'] ?? '',
            gWalletCustomerId: $data['g_wallet_customer_id'] ?? '',
            ebarimtReceiverType: $data['ebarimt_receiver_type'] ?? '',
            ebarimtReceiver: $data['ebarimt_receiver'] ?? '',
            ebarimtDistrictCode: $data['ebarimt_district_code'] ?? '',
            ebarimtBillType: $data['ebarimt_bill_type'] ?? '',
            gMerchantId: $data['g_merchant_id'] ?? '',
            merchantBranchCode: $data['merchant_branch_code'] ?? '',
            merchantTerminalCode: $data['merchant_terminal_code'] ?? null,
            merchantStaffCode: $data['merchant_staff_code'] ?? null,
            merchantRegisterNo: $data['merchant_register_no'] ?? '',
            gPaymentId: $data['g_payment_id'] ?? '',
            paidBy: $data['paid_by'] ?? '',
            objectType: $data['object_type'] ?? '',
            objectId: $data['object_id'] ?? '',
            amount: $data['amount'] ?? '',
            vatAmount: $data['vat_amount'] ?? '',
            cityTaxAmount: $data['city_tax_amount'] ?? '',
            ebarimtQrData: $data['ebarimt_qr_data'] ?? '',
            ebarimtLottery: $data['ebarimt_lottery'] ?? '',
            note: $data['note'] ?? null,
            barimtStatus: $data['barimt_status'] ?? '',
            barimtStatusDate: $data['barimt_status_date'] ?? '',
            ebarimtSentEmail: $data['ebarimt_sent_email'] ?? null,
            ebarimtReceiverPhone: $data['ebarimt_receiver_phone'] ?? '',
            taxType: $data['tax_type'] ?? '',
            merchantTin: $data['merchant_tin'] ?? '',
            ebarimtReceiptId: $data['ebarimt_receipt_id'] ?? '',
            createdBy: $data['created_by'] ?? '',
            createdDate: $data['created_date'] ?? '',
            updatedBy: $data['updated_by'] ?? '',
            updatedDate: $data['updated_date'] ?? '',
            status: (bool) ($data['status'] ?? false),
            barimtItems: $data['barimt_items'] ?? [],
            barimtTransactions: $data['barimt_transactions'] ?? [],
            barimtHistories: $data['barimt_histories'] ?? [],
        );
    }
}
