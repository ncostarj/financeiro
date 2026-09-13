<?php

namespace App\Imports;

use App\Models\Fatura;
use App\Models\Lancamento;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as DateExcel;

class LancamentoImportExcel implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    private Fatura $fatura;

    public function __construct(Fatura $fatura)
    {
        $this->fatura = $fatura;
    }

    public function onRow(Row $row)
    {
        // No OnEachRow, pegamos o array de dados com o método ->toArray()
        $rowData = $row->toArray();

        if(isset($rowData['lancamento']) && mb_strtolower(trim($rowData['lancamento'])) === 'pagamento efetuado') {
            return;
        }

        if (isset($rowData['parcelamento']) && mb_strtolower(trim($rowData['parcelamento'])) === 'subtotal') {
            return; // No OnEachRow usamos return vazio para pular
        }

        if (!isset($rowData['data']) || !is_numeric($rowData['data'])) {
            return; 
        }

        $data = DateExcel::excelToDateTimeObject($rowData['data'])->format('Y-m-d') ?? '';
        $descricao = $rowData['lancamento'] ?? '';
        $parcelado = $rowData['parcelamento'] != null;
        $parcela = 0;
        $qtd_parcelas = 0;
        $moeda_estrangeira = $rowData['5'] != null;
        $moeda = 'BRL';
        $valor_moeda = 0;
        $cotacao = 0;
        $nome_titular = $rowData['nome'];

        if ($parcelado) {
            preg_match('/Parcela\s(?<parcela>[0-9]+)\sde\s(?<qtd_parcelas>[0-9]+)/', $rowData['parcelamento'], $matches1);
            $parcela = $matches1['parcela'] ?? 0;
            $qtd_parcelas = $matches1['qtd_parcelas'] ?? 0;
        }

        if ($moeda_estrangeira) {
            preg_match('/(?<moeda>[A-Z]{3})\x{00A0}(?<valor_original>[0-9,]+)\s*\(cotação\s*R\$\x{00A0}(?<cotacao>[0-9,]+)\)/u', $rowData['5'], $matches2);
            $moeda = $matches2['moeda'] ?? 'BRL';
            $valor_moeda = isset($matches2['valor_original']) ? str_replace(',','.', $matches2['valor_original']) : 0;
            $cotacao = isset($matches2['cotacao']) ? str_replace(',','.', $matches2['cotacao']) : 0;
        }

        if($nome_titular === "Newton Gonzaga Costa") {
            $nome_titular = "Newton G Costa";
        }

        // Criamos a instância APENAS com os dados fillable
        $lancamento = new Lancamento([
            "data"              => $data,
            "descricao"         => $descricao,
            "parcelado"         => $parcelado,
            "parcela"           => $parcela,
            "qtd_parcelas"      => $qtd_parcelas,
            "moeda_estrangeira" => $moeda_estrangeira,
            "moeda"             => $moeda,
            "valor_moeda"       => $valor_moeda,
            "cotacao"           => $cotacao,
            "valor"             => $rowData['valor'] ?? '',
            "tipo_cartao"       => $rowData['tipo_do_cartao'] ?? '',
            "numero_cartao"     => ltrim($rowData['numero_do_cartao'] ?? '', '*'),
            "nome_titular"      => $nome_titular,
        ]);

        // 🌟 ASSOCIAÇÃO DO RELACIONAMENTO CORRETA:
        // O Laravel injeta o fatura_id automaticamente e salva no banco de dados.
        $this->fatura->lancamentos()->save($lancamento);
    }

    public function headingRow(): int
    {
        return 14;
    }
}
