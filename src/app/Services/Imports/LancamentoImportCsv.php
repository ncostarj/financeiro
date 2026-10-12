<?php

namespace App\Imports;

use App\Models\Fatura;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Row;

/**
 * Importador para o CSV de fatura do Nubank, que traz apenas 3 colunas:
 * "date", "title" e "amount" — bem mais enxuto que o extrato do Itaú.
 * Como não há parcelamento, moeda estrangeira, titular ou número de
 * cartão nesse formato, esses campos são normalizados como vazios/nulos
 * antes de delegar para a lógica comum em LancamentoImportBase.
 */
class LancamentoImportCsv extends LancamentoImportBase implements OnEachRow, WithCustomCsvSettings
{
    /**
     * O CSV do Nubank já traz o cabeçalho na 1ª linha (sem preâmbulo).
     */
    private const HEADING_ROW = 1;

    /**
     * O Nubank exporta a data em ISO (ex: "2026-01-31").
     */
    private const FORMATO_DATA = 'Y-m-d';

    /**
     * Como este CSV não tem uma coluna "nome" para identificar o titular
     * (diferente do extrato do Itaú), quem instanciar esta classe pode
     * informar um titular padrão — ex: `new LancamentoImportCsv($fatura, 'newton')`.
     * Sem isso, o campo fica em branco e o agrupamento por titular no
     * resumo da fatura não vai funcionar como esperado.
     */
    private ?string $titularPadrao;

    public function __construct(string $nomeArquivo, Fatura $fatura, ?string $titularPadrao = null)
    {
        parent::__construct($nomeArquivo, $fatura);
        $this->titularPadrao = $titularPadrao;
    }

    /**
     * O Nubank usa vírgula como separador de campos E vírgula como separador decimal,
     * mas os valores monetários vêm entre aspas duplas (ex: "40,95"), o que permite
     * ao parser distinguir o delimitador de campo do decimal dentro do valor.
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'input_encoding' => 'UTF-8',
        ];
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();

        // Não existe coluna "lancamento"/"parcelamento" nesse formato, então o
        // filtro de "pagamento efetuado" do Itaú (deveIgnorarLinha) não se aplica.
        // O Nubank costuma nomear o pagamento da fatura recebido como algo como
        // "Pagamento recebido" no campo "title" — ajuste o texto abaixo conforme
        // aparecer no seu extrato real.
        $tituloStr = mb_strtolower(trim($rowData['title'] ?? ''));

        if (str_contains($tituloStr, 'pagamento recebido')) {
            return;
        }

        if (empty($rowData['date']) || !isset($rowData['amount']) || $rowData['amount'] === '') {
            return;
        }

        $dataVencimento = Carbon::createFromFormat(self::FORMATO_DATA, trim($rowData['date']))->format('Y-m-d');

        // O Nubank exporta o amount no formato pt-BR com vírgula decimal (ex: "40,95").
        // Não pode fazer cast direto para float — passa pelo parseValor() para tratar corretamente.
        // Valores de estorno/crédito vêm como "- 27,22" (com espaço), por isso o trim extra.
        $amountLimpo = str_replace(' ', '', trim($rowData['amount']));
        $valorFormatado = $this->parseValor($amountLimpo);

        // Extrai parcelamento do título, que no Nubank vem embutido no formato "Descrição - Parcela N/M"
        $titulo = $rowData['title'] ?? '';
        $parcelamento = null;

        if (preg_match('/\s*-\s*Parcela\s+(\d+)\/(\d+)\s*$/i', $titulo, $matchParcela)) {
            $parcelamento = "Parcela {$matchParcela[1]} de {$matchParcela[2]}";
            // Remove a parte de parcelamento da descrição para ficar limpo
            $titulo = preg_replace('/\s*-\s*Parcela\s+\d+\/\d+\s*$/i', '', $titulo);
        }

        $rowDataNormalizado = [
            'lancamento'       => $titulo,
            'parcelamento'     => $parcelamento,
            'nome'             => $this->titularPadrao ?? '',
            'tipo_do_cartao'   => '',
            'numero_do_cartao' => '',
        ];

        $this->processarLinha($rowDataNormalizado, $dataVencimento, $valorFormatado);
    }

    public function headingRow(): int
    {
        return self::HEADING_ROW;
    }
}