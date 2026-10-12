<?php

namespace App\Exports\Sheets;

use App\Models\Lancamento;
use Carbon\Carbon;

class LancamentosSheet extends BaseSheet
{
    public function collection()
    {
        return Lancamento::query()
            ->with(['fatura', 'categoria'])
            ->orderBy('data_vencimento')
            ->get()
            ->map(fn (Lancamento $lancamento) => [
                $lancamento->descricao,
                $lancamento->data_vencimento ? Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '',
                (float) $lancamento->valor,
                $lancamento->categoria->nome ?? 'Sem Categoria',
                $this->tituloTitular($lancamento->nome_titular),
                $lancamento->fatura?->data_vencimento
                    ? Carbon::parse($lancamento->fatura->data_vencimento)->format('m/Y')
                    : '',
                ucfirst($lancamento->status),
            ]);
    }

    public function headings(): array
    {
        return ['Descrição', 'Data Lançamento', 'Valor (R$)', 'Categoria', 'Titular', 'Mês da Fatura', 'Status'];
    }

    public function title(): string
    {
        return 'Lancamentos';
    }

    /**
     * Mesma normalização usada em DashboardSheet: titular em branco (ex: linhas
     * vindas do CSV do Nubank sem titular informado) vira "Sem Titular", para
     * que o valor bata com o que aparece no dropdown de filtro do Dashboard.
     */
    private function tituloTitular(?string $nomeTitular): string
    {
        return $nomeTitular === null || $nomeTitular === '' ? 'Sem Titular' : $nomeTitular;
    }
}