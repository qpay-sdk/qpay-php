<?php

declare(strict_types=1);

namespace QPay\Models;

class InvoiceResponse
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly string $qrText,
        public readonly string $qrImage,
        public readonly string $qPayShortUrl,
        public readonly array $urls,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $urls = array_map(
            fn(array $url) => [
                'name' => $url['name'] ?? '',
                'description' => $url['description'] ?? '',
                'logo' => $url['logo'] ?? '',
                'link' => $url['link'] ?? '',
            ],
            $data['urls'] ?? [],
        );

        return new self(
            invoiceId: $data['invoice_id'] ?? '',
            qrText: $data['qr_text'] ?? '',
            qrImage: $data['qr_image'] ?? '',
            qPayShortUrl: $data['qPay_shortUrl'] ?? '',
            urls: $urls,
        );
    }
}
