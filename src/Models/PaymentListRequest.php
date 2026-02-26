<?php

declare(strict_types=1);

namespace QPay\Models;

class PaymentListRequest
{
    public function __construct(
        public readonly string $objectType,
        public readonly string $objectId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $pageNumber,
        public readonly int $pageLimit,
    ) {
    }

    public function toArray(): array
    {
        return [
            'object_type' => $this->objectType,
            'object_id' => $this->objectId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'offset' => [
                'page_number' => $this->pageNumber,
                'page_limit' => $this->pageLimit,
            ],
        ];
    }
}
