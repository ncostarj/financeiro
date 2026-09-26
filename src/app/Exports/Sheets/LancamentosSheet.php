<?php

namespace App\Exports\Sheets;

use App\Models\Lancamento;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LancamentosSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
   
      public function query()
    {
        return Lancamento::query()->with('categoria');
    }

    public function title(): string
    {
        return 'Lançamentos';
    }

    public function headings(): array
    {
        return [
            'Fatura de',
            'Meio de Pagamento',
            'Categoria',
            'Titular',
            'Descrição',
            'Tipo',
            'Status',
            'Dt. Venc.',
            'Dt. Pgto.',
            'Valor',
            'Parcela',
            'Qtd. Parcelas',
        ];
    }

    public function map($lancamento): array
    {
        return [
            $lancamento->fatura ? $lancamento->fatura->data_vencimento->format('m/Y') : null,
            $lancamento->meioPagamento->nome,
            $lancamento->categoria?->nome??'Sem Categoria',
            $lancamento->nome_titular,
            $lancamento->descricao,
            $lancamento->tipo,
            $lancamento->status,
            $lancamento->data_vencimento->format('d/m/Y'),
            $lancamento->data_pagamento?$lancamento->data_pagamento->format('d/m/Y') : null,
            $lancamento->valor,
            $lancamento->parcela,
            $lancamento->qtd_parcelas,
        ];
    }
}