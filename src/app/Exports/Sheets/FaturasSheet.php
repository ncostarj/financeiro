<?php

namespace App\Exports\Sheets;

use App\Models\Fatura;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class FaturasSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Fatura::query()->with('meioPagamento');
    }

    public function title(): string
    {
        return 'Faturas';
    }

    public function headings(): array
    {
        return [
            'Meio de Pagamento',
            'Data Fechamento',
            'Data Vencimento',
            'Total (R$)',
            'Status',
        ];
    }

    public function map($fatura): array
    {
        return [
            $fatura->meioPagamento?->nome ?? 'N/A',
            $fatura->data_fechamento?->format('d/m/Y'),
            $fatura->data_vencimento?->format('d/m/Y'),
            $fatura->total,
            strtoupper($fatura->status),
        ];
    }
}