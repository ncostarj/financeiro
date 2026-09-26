<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as DateExcel;

class LancamentoImportExcel extends LancamentoImportBase implements OnEachRow
{
    public function onRow(Row $row)
    {
        $rowData = $row->toArray();

        if ($this->deveIgnorarLinha($rowData)) {
            return;
        }

        // No XLS, a coluna "data" vem como número serial do Excel.
        if (!isset($rowData['data']) || !is_numeric($rowData['data'])) {
            return;
        }

        $dataVencimento = DateExcel::excelToDateTimeObject($rowData['data'])->format('Y-m-d');
        $valorFormatado = $this->parseValor($rowData['valor'] ?? 0);

        $this->processarLinha($rowData, $dataVencimento, $valorFormatado);
    }

    public function headingRow(): int
    {
        // O extrato em XLS do Itaú tem um preâmbulo de 13 linhas antes do cabeçalho real.
        return 14;
    }
}