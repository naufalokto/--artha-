<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function array(): array
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Customer Name',
            'Total Amount',
            'Status',
            'Created At',
            'Updated At'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction['transaction_id'] ?? '',
            $transaction['customer_name'] ?? '',
            'Rp ' . number_format($transaction['total_amount'] ?? 0, 2, ',', '.'),
            $transaction['status'] ?? '',
            $transaction['created_at'] ? date('Y-m-d H:i:s', strtotime($transaction['created_at'])) : '',
            $transaction['updated_at'] ? date('Y-m-d H:i:s', strtotime($transaction['updated_at'])) : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ]
        ];
    }
} 