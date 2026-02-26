<?php

declare(strict_types=1);

namespace QPay\Models;

class CreateEbarimtRequest
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $ebarimtReceiverType,
        public readonly ?string $ebarimtReceiver = null,
        public readonly ?string $districtCode = null,
        public readonly ?string $classificationCode = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'payment_id' => $this->paymentId,
            'ebarimt_receiver_type' => $this->ebarimtReceiverType,
        ];

        if ($this->ebarimtReceiver !== null) {
            $data['ebarimt_receiver'] = $this->ebarimtReceiver;
        }
        if ($this->districtCode !== null) {
            $data['district_code'] = $this->districtCode;
        }
        if ($this->classificationCode !== null) {
            $data['classification_code'] = $this->classificationCode;
        }

        return $data;
    }
}
