<?php

namespace App\Imports;

use App\Models\Fatura;
use App\Models\Lancamento;
use App\Services\CategoriaService;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

abstract class LancamentoImportBase implements WithHeadingRow, SkipsEmptyRows
{
    protected Fatura $fatura;
    protected CategoriaService $categoriaService;

    public function __construct(Fatura $fatura)
    {
        $this->fatura = $fatura;
        $this->categoriaService = new CategoriaService();
    }

    /**
     * Linhas de resumo/subtotal do extrato, que devem ser ignoradas
     * independentemente do formato de origem (xls ou csv).
     */
    protected function deveIgnorarLinha(array $rowData): bool
    {
        $lancamentoStr = mb_strtolower(trim($rowData['lancamento'] ?? ''));
        $parcelamentoStr = mb_strtolower(trim($rowData['parcelamento'] ?? ''));

        return $lancamentoStr === 'pagamento efetuado' || $parcelamentoStr === 'subtotal';
    }

    /**
     * Converte um valor monetário no formato brasileiro (ex: "1.234,56") para float.
     * Comum aos dois formatos, já que ambos trazem o valor como texto no padrão pt-BR.
     */
    protected function parseValor($valorBruto): float
    {
        if (is_string($valorBruto) && strpos($valorBruto, ',') !== false) {
            return (float) str_replace(',', '.', str_replace('.', '', $valorBruto));
        }

        return (float) $valorBruto;
    }

    /**
     * Lógica comum de tratamento e persistência de uma linha já normalizada.
     * Cada importador específico é responsável por extrair $dataVencimento
     * e $valorFormatado da forma correta para o seu formato de arquivo,
     * e então delegar o resto para este método.
     */
    protected function processarLinha(array $rowData, string $dataVencimento, float $valorFormatado): void
    {
        $descricao = $rowData['lancamento'] ?? '';

        // Regras de Parcelamento
        $parcelado = !empty($rowData['parcelamento']);
        $parcela = 0;
        $qtd_parcelas = 0;

        if ($parcelado && preg_match('/Parcela\s(?<parcela>\d+)\sde\s(?<qtd_parcelas>\d+)/i', $rowData['parcelamento'], $matches1)) {
            $parcela = $matches1['parcela'];
            $qtd_parcelas = $matches1['qtd_parcelas'];
        }

        // Regras de Moeda Estrangeira
        $valoresColuna = array_values($rowData);
        $colunaMoeda = $valoresColuna[5] ?? null;

        $moeda_estrangeira = !empty($colunaMoeda);
        $moeda = 'BRL';
        $valor_moeda = 0;
        $cotacao = 0;

        if ($moeda_estrangeira && preg_match('/(?<moeda>[A-Z]{3})\x{00A0}(?<valor_original>[0-9,.]+)\s*\(cotação\s*R\$\x{00A0}(?<cotacao>[0-9,.]+)\)/u', $colunaMoeda, $matches2)) {
            $moeda = $matches2['moeda'];
            $valor_moeda = (float) str_replace(',', '.', $matches2['valor_original']);
            $cotacao = (float) str_replace(',', '.', $matches2['cotacao']);
        }

        // Tratamento de Titulares (case-insensitive)
        $nome_titular_raw = mb_strtolower($rowData['nome'] ?? '');
        $nome_titular = $nome_titular_raw;

        if (str_contains($nome_titular_raw, 'newton')) {
            $nome_titular = 'newton';
        } elseif (str_contains($nome_titular_raw, 'anna')) {
            $nome_titular = 'carol';
        } elseif (str_contains($nome_titular_raw, 'marli')) {
            $nome_titular = 'marli';
        }

        // Instância e Relacionamentos
        $lancamento = new Lancamento([
            "descricao"         => $descricao,
            "tipo"              => 'despesa',
            "status"            => $this->fatura->status === 'pago' ? 'pago' : 'pendente',
            "data_vencimento"   => $dataVencimento,
            "data_pagamento"    => $this->fatura->status === 'pago' ? $this->fatura->data_vencimento : null,
            "valor"             => $valorFormatado,
            "moeda_estrangeira" => $moeda_estrangeira,
            "moeda"             => $moeda,
            "cotacao"           => $cotacao,
            "valor_moeda"       => $valor_moeda,
            "parcelado"         => $parcelado,
            "parcela"           => $parcela,
            "qtd_parcelas"      => $qtd_parcelas,
            "tipo_cartao"       => $rowData['tipo_do_cartao'] ?? '',
            "numero_cartao"     => ltrim($rowData['numero_do_cartao'] ?? '', '*'),
            "nome_titular"      => $nome_titular,
        ]);

        $lancamento
            ->meioPagamento()->associate($this->fatura->meioPagamento)
            ->categoria()->associate($this->categoriaService->identificarCategoriaLancamento($lancamento));

        $this->fatura->lancamentos()->save($lancamento);
    }
}