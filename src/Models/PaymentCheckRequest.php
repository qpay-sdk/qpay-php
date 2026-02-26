<?php

declare(strict_types=1);

namespace QPay\Models;

class PaymentCheckRequest
{
    public function __construct(
        public readonly string $objectType,
        public readonly string $objectId,
        public readonly ?int $pageNumber = null,
        public readonly ?int $pageLimit = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'object_type' => $this->objectType,
            'object_id' => $this->objectId,
        ];

        if ($this->pageNumber !== null && $this->pageLimit !== null) {
            $data['offset'] = [
                'page_number' => $this->pageNumber,
                'page_limit' => $this->pageLimit,
            ];
        }

        return $data;
    }
}
