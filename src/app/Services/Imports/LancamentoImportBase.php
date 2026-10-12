<?php

namespace App\Imports;

use App\Models\Fatura;
use App\Models\Lancamento;
use App\Models\MeioPagamento;
use App\Services\CategoriaService;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

abstract class LancamentoImportBase implements WithHeadingRow, SkipsEmptyRows
{
    protected string $nomeArquivo;

    /** Preenchido pelos importadores de fatura */
    protected ?Fatura $fatura = null;

    /** Preenchido pelos importadores de extrato */
    protected ?MeioPagamento $meioPagamento = null;

    protected CategoriaService $categoriaService;

    public function __construct(string $nomeArquivo, Fatura|MeioPagamento $origem)
    {
        $this->nomeArquivo = $nomeArquivo;
        $this->categoriaService = new CategoriaService();

        if ($origem instanceof Fatura) {
            $this->fatura = $origem;
        } else {
            $this->meioPagamento = $origem;
        }
    }

    /**
     * Linhas de resumo/subtotal que devem ser ignoradas
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
     */
    protected function parseValor($valorBruto): float
    {
        if (is_string($valorBruto) && strpos($valorBruto, ',') !== false) {
            return (float) str_replace(',', '.', str_replace('.', '', $valorBruto));
        }

        return (float) $valorBruto;
    }

    /**
     * Persiste um lançamento de FATURA (Itaú XLSX, Nubank CSV).
     * Salva tudo em um único registro na tabela lancamentos.
     */
    protected function processarLinha(array $rowData, string $dataVencimento, float $valorFormatado): void
    {
        $descricao = $rowData['lancamento'] ?? '';

        // --- Parcelamento ---
        $parcelado = !empty($rowData['parcelamento']);
        $parcela = 0;
        $qtd_parcelas = 0;

        if ($parcelado && preg_match('/Parcela\s(?<parcela>\d+)\sde\s(?<qtd_parcelas>\d+)/i', $rowData['parcelamento'], $m)) {
            $parcela = (int) $m['parcela'];
            $qtd_parcelas = (int) $m['qtd_parcelas'];
        }

        if ($parcelado && preg_match('/Parcela\s(?<parcela>\d+)\s\/\s(?<qtd_parcelas>\d+)/i', $rowData['lancamento'], $m)) {
            $parcela = (int) $m['parcela'];
            $qtd_parcelas = (int) $m['qtd_parcelas'];
        }

        // --- Moeda Estrangeira ---
        $valoresColuna = array_values($rowData);
        $colunaMoeda = $valoresColuna[5] ?? null;

        $moeda_estrangeira = !empty($colunaMoeda);
        $moeda = 'BRL';
        $valor_moeda = null;
        $cotacao = null;

        if ($moeda_estrangeira && preg_match('/(?<moeda>[A-Z]{3})\x{00A0}(?<valor_original>[0-9,.]+)\s*\(cotação\s*R\$\x{00A0}(?<cotacao>[0-9,.]+)\)/u', $colunaMoeda, $m)) {
            $moeda = $m['moeda'];
            $valor_moeda = (float) str_replace(',', '.', $m['valor_original']);
            $cotacao = (float) str_replace(',', '.', $m['cotacao']);
        }

        // --- Titular ---
        $nome_titular = $this->normalizarTitular($rowData['nome'] ?? '');

        // --- Persiste tudo num único Lancamento ---
        $lancamento = new Lancamento([
            'descricao'         => $descricao,
            'tipo'              => 'despesa',
            'status'            => $this->fatura->status === 'pago' ? 'pago' : 'pendente',
            'origem'            => 'fatura',
            'data_vencimento'   => $dataVencimento,
            'data_pagamento'    => $this->fatura->status === 'pago' ? $this->fatura->data_vencimento : null,
            'valor'             => $valorFormatado,
            'parcelado'         => $parcelado,
            'parcela'           => $parcela,
            'qtd_parcelas'      => $qtd_parcelas,
            'moeda_estrangeira' => $moeda_estrangeira,
            'moeda'             => $moeda,
            'valor_moeda'       => $valor_moeda,
            'cotacao'           => $cotacao,
            'tipo_cartao'       => $rowData['tipo_do_cartao'] ?? null,
            'numero_cartao'     => ltrim($rowData['numero_do_cartao'] ?? '', '*') ?: null,
            'nome_titular'      => $nome_titular ?: null,
        ]);

        $lancamento
            ->meioPagamento()->associate($this->fatura->meioPagamento);
            // ->categoria()->associate($this->categoriaService->identificarCategoriaLancamento($lancamento));

        $this->fatura->lancamentos()->save($lancamento);
    }

    /**
     * Padrões que identificam entradas de dinheiro (receita) independente do sinal.
     */
    private const DESCRICOES_RECEITA = [
        '/pix\s+recebido/i',
        '/pix\s+receb/i',
        '/credito\s+liberad/i',
    ];

    /**
     * Padrões que identificam saídas de dinheiro (despesa) independente do sinal.
     */
    private const DESCRICOES_DESPESA = [
        '/pix\s+transf/i',
        '/pix\s+enviado/i',
        '/ted\s+\d/i',
        '/pix\s+qrs/i',
        '/pix\s+cartao/i',
        '/pix\s+origem\s+cartao/i',
    ];

    /**
     * Persiste um lançamento de EXTRATO (Itaú XLS, Bradesco XLS).
     * Salva tudo num único Lancamento sem fatura_id.
     */
    protected function processarLinhaExtrato(string $descricao, string $data, float $valor): void
    {
        $tipo = $this->detectarTipoExtrato($descricao, $valor);
        $valorAbsoluto = abs($valor);

        $lancamento = new Lancamento([
            'descricao'         => $descricao,
            'tipo'              => $tipo,
            'status'            => 'pago',
            'origem'            => 'extrato',
            'data_vencimento'   => $data,
            'data_pagamento'    => $data,
            'valor'             => $valorAbsoluto,
            'parcelado'         => false,
            'parcela'           => 1,
            'qtd_parcelas'      => 1,
            'moeda_estrangeira' => false,
            'moeda'             => 'BRL',
        ]);

        $lancamento
            ->meioPagamento()->associate($this->meioPagamento);
            // ->categoria()->associate($this->categoriaService->identificarCategoriaLancamento($lancamento));

        $lancamento->save();
    }

    /**
     * Normaliza o nome do titular para um identificador curto e consistente.
     */
    protected function normalizarTitular(string $nomeRaw): string
    {
        $nome = mb_strtolower(trim($nomeRaw));

        if (str_contains($this->nomeArquivo, 'itau')) {
            if (str_contains($nome, 'newton')) return 'newton';
            if (str_contains($nome, 'anna'))   return 'carol';
            if (str_contains($nome, 'marli'))  return 'marli';
        }

        if (str_contains($this->nomeArquivo, 'nubank')) {
            return 'marilene';
        }

        return $nome;
    }

    /**
     * Determina o tipo do lançamento de extrato pela descrição, usando o sinal como fallback.
     */
    private function detectarTipoExtrato(string $descricao, float $valor): string
    {
        foreach (self::DESCRICOES_RECEITA as $pattern) {
            if (preg_match($pattern, $descricao)) {
                return 'receita';
            }
        }

        foreach (self::DESCRICOES_DESPESA as $pattern) {
            if (preg_match($pattern, $descricao)) {
                return 'despesa';
            }
        }

        return $valor >= 0 ? 'receita' : 'despesa';
    }
}
