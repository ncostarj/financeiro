<?php

namespace App\Exports\Sheets;

use App\Models\Fatura;
use Carbon\Carbon;

class FaturasSheet extends BaseSheet
{
    public function collection()
    {
        return Fatura::query()
            ->with('meioPagamento')
            ->orderBy('data_vencimento')
            ->get()
            ->map(fn (Fatura $fatura) => [
                $fatura->meioPagamento->nome ?? '',
                $fatura->data_fechamento ? Carbon::parse($fatura->data_fechamento)->format('d/m/Y') : '',
                $fatura->data_vencimento ? Carbon::parse($fatura->data_vencimento)->format('d/m/Y') : '',
                ucfirst($fatura->status),
                (float) $fatura->total,
            ]);
    }

    public function headings(): array
    {
        return ['Meio de Pagamento', 'Data Fechamento', 'Data Vencimento', 'Status', 'Total (R$)'];
    }

    public function title(): string
    {
        return 'Faturas';
    }
}