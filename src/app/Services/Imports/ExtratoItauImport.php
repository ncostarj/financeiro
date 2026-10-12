<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Row;

/**
 * Importador para o extrato de conta corrente do Itaú (formato XLS).
 *
 * Estrutura do arquivo:
 * - Linhas 1–8: preamble institucional (logotipo, nome, agência, conta, etc.)
 * - Linha 9: cabeçalho real — data | lançamento | ag./origem | valor (R$) | saldos (R$)
 * - Linha 10: subcabeçalho "lançamentos" — ignorado por WithHeadingRow
 * - Demais linhas: lançamentos reais
 *
 * Particularidades:
 * - "valor (R$)" já vem como float (positivo = crédito, negativo = débito).
 * - Linhas "SALDO ANTERIOR" e "SALDO TOTAL DISPONÍVEL DIA" são marcadores
 *   de saldo do período/dia e devem ser ignoradas — não são lançamentos.
 * - Lançamentos sem data (coluna A vazia ou não numérica) são ignorados.
 */
class ExtratoItauImport extends LancamentoImportBase implements OnEachRow, WithMultipleSheets
{
    /** Descrições de linhas que representam saldo e devem ser ignoradas. */
    private const LINHAS_IGNORAR = [
        'saldo anterior',
        'saldo total disponível dia',
        'saldo total dispon', // fallback para encoding incompleto
    ];

    public function onRow(Row $row): void
    {
        $rowData = $row->toArray();

        // Ignora linhas sem data (ex: subcabeçalho "lançamentos" na linha 10)
        $dataRaw = $rowData['data'] ?? null;
        if (empty($dataRaw)) {
            return;
        }

        $descricao = mb_strtolower(trim($rowData['lancamento'] ?? ''));

        // Ignora linhas de saldo
        foreach (self::LINHAS_IGNORAR as $ignorar) {
            if (str_contains($descricao, $ignorar)) {
                return;
            }
        }

        // "valor (R$)" já é float no XLS do Itaú — não precisa de parseValor()
        $valor = (float) ($rowData['valor_r'] ?? $rowData['valor_(r$)'] ?? $rowData['valor_r$'] ?? 0);

        if ($valor === 0.0) {
            return;
        }

        // Data vem como string "dd/mm/yyyy" no extrato do Itaú
        $data = \DateTime::createFromFormat('d/m/Y', trim($dataRaw));
        if (!$data) {
            return;
        }

        $this->processarLinhaExtrato(
            descricao: trim($rowData['lancamento'] ?? ''),
            data: $data->format('Y-m-d'),
            valor: $valor
        );
    }

    public function headingRow(): int
    {
        // O preamble do extrato Itaú ocupa 8 linhas; o cabeçalho real está na linha 9.
        return 9;
    }

    public function sheets(): array
    {
        // Processa apenas a aba "Lançamentos" (índice 0); ignora "Posição Consolidada" e "Limites"
        return [0 => $this];
    }
}
