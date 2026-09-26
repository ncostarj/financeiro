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

    public function __construct(Fatura $fatura, ?string $titularPadrao = null)
    {
        parent::__construct($fatura);
        $this->titularPadrao = $titularPadrao;
    }

    /**
     * O Nubank usa vírgula como delimitador normalmente, já que o "amount"
     * vem com ponto decimal (ex: "23.90"), sem risco de confundir a coluna.
     * TODO: confirme abrindo o CSV num editor de texto, caso perceba dados
     * indo parar na coluna errada.
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

        // O Nubank já usa ponto decimal (ex: "23.90"), então não passa pelo
        // parseValor() pensado para o formato brasileiro (vírgula decimal).
        $valorFormatado = (float) $rowData['amount'];

        $rowDataNormalizado = [
            'lancamento' => $rowData['title'] ?? '',
            'parcelamento' => null,
            'nome' => $this->titularPadrao ?? '',
            'tipo_do_cartao' => '',
            'numero_do_cartao' => '',
        ];

        $this->processarLinha($rowDataNormalizado, $dataVencimento, $valorFormatado);
    }

    public function headingRow(): int
    {
        return self::HEADING_ROW;
    }
}