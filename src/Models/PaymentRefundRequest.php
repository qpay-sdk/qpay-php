<?php

declare(strict_types=1);

namespace QPay\Models;

class PaymentRefundRequest
{
    public function __construct(
        public readonly ?string $callbackUrl = null,
        public readonly ?string $note = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->callbackUrl !== null) {
            $data['callback_url'] = $this->callbackUrl;
        }
        if ($this->note !== null) {
            $data['note'] = $this->note;
        }

        return $data;
    }
}
