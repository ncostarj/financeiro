<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

/**
 * Importador para o extrato de conta corrente do Bradesco (formato XLS).
 *
 * Estrutura do arquivo:
 * - Linhas 1–7: preamble institucional (banco, data de exportação, período, agência/conta)
 * - Linha 8: cabeçalho real — Data | Histórico | Docto. | Crédito (R$) | Débito (R$) | Saldo (R$)
 * - Demais linhas: lançamentos reais
 *
 * Particularidades:
 * - Crédito e Débito vêm em colunas separadas como strings pt-BR (ex: "1.140,49", "-241,15").
 *   Apenas uma das duas é preenchida por linha.
 * - Cada lançamento ocupa duas linhas:
 *   - Linha principal: tem Data, Histórico, Docto., Crédito ou Débito, Saldo
 *   - Linha complementar: Data vazia, Histórico com descrição adicional (ex: "Rem: Newton...")
 *   A linha complementar é ignorada (sem data = skip).
 * - "SALDO ANTERIOR" é a primeira linha de dados — deve ser ignorada.
 * - Linha "Total" ao final — deve ser ignorada.
 */
class ExtratoBradescoImport extends LancamentoImportBase implements OnEachRow
{
    private const LINHAS_IGNORAR = [
        'saldo anterior',
        'total',
    ];

    public function onRow(Row $row): void
    {
        $rowData = $row->toArray();

        // Linhas complementares (sem data) são descrições adicionais do lançamento anterior
        $dataRaw = trim($rowData['data'] ?? '');
        if (empty($dataRaw)) {
            return;
        }

        $historico = mb_strtolower(trim($rowData['historico'] ?? ''));

        // Ignora linhas de saldo e totais
        foreach (self::LINHAS_IGNORAR as $ignorar) {
            if (str_contains($historico, $ignorar)) {
                return;
            }
        }

        // Crédito e Débito em colunas separadas — apenas uma é preenchida por linha
        $creditoRaw = trim($rowData['credito_r'] ?? $rowData['credito_(r$)'] ?? $rowData['credito_r$'] ?? '');
        $debitoRaw  = trim($rowData['debito_r']  ?? $rowData['debito_(r$)']  ?? $rowData['debito_r$']  ?? '');

        $valor = 0.0;

        if (!empty($creditoRaw)) {
            // Crédito = entrada de dinheiro na conta (valor positivo)
            $valor = $this->parseValor($creditoRaw);
        } elseif (!empty($debitoRaw)) {
            // Débito = saída de dinheiro (pode vir com sinal negativo ou sem)
            $valorDebito = $this->parseValor($debitoRaw);
            // Garante que débito seja negativo independente do sinal no arquivo
            $valor = $valorDebito > 0 ? -$valorDebito : $valorDebito;
        }

        if ($valor === 0.0) {
            return;
        }

        // Data vem como string "dd/mm/yy" (2 dígitos no ano) no extrato do Bradesco
        $data = \DateTime::createFromFormat('d/m/y', $dataRaw)
             ?: \DateTime::createFromFormat('d/m/Y', $dataRaw);

        if (!$data) {
            return;
        }

        $this->processarLinhaExtrato(
            descricao: trim($rowData['historico'] ?? ''),
            data: $data->format('Y-m-d'),
            valor: $valor
        );
    }

    public function headingRow(): int
    {
        // O preamble do Bradesco ocupa 7 linhas; o cabeçalho real está na linha 8.
        return 8;
    }
}
