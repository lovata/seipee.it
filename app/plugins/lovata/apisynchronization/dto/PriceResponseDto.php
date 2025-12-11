<?php namespace Lovata\ApiSynchronization\Dto;

class PriceResponseDto
{
    private function __construct(
        public int     $totalRecords,
        public int     $columnOrder,
        public string  $productCode,
        public string  $baseProductCode,
        public float   $price,
        public int     $list,
        public float   $netPrice,
    )
    {
    }

    static public function fromApiResponse(?array $data): ?self
    {
        if (empty($data)) {
            return null;
        }

        return new PriceResponseDto(
            totalRecords: (int) ($data['TotRec'] ?? 0),
            columnOrder: (int) ($data['ColOrder'] ?? 0),
            productCode: (string) ($data['CD_AR'] ?? ''),
            baseProductCode: (string) ($data['CD_AR_Base'] ?? ''),
            price: (float) ($data['Prezzo'] ?? 0.0),
            list: (int) ($data['List'] ?? 0),
            netPrice: (float) ($data['PrezzoN'] ?? 0.0),
        );
    }
}
