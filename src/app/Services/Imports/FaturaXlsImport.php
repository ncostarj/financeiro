<?php

namespace App\Services\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as DateExcel;

class FaturaXlsImport extends FaturaImportBase implements WithHeadingRow, SkipsEmptyRows, ToCollection
{
    public function headingRow(): int
    {
        return 14;
    }

    public function collection(Collection $rows): void
    {
        $this->dadosNormalizados = $rows->filter(function ($row) {
            // Verifica se a coluna 'data' existe e se é um valor numérico (timestamp do Excel)
            // Isso vai pular automaticamente linhas de totais, rodapés ou textos indesejados.
            return isset($row['data']) && is_numeric($row['data']);
        })->map(function ($row) {
            return [
                'data'         => DateExcel::excelToDateTimeObject($row['data'])->format('Y-m-d'),
                'descricao'    => trim($row['lancamento']),
                'valor'        => (float) $row['valor'],
                'origem'       => 'Itaú', // Identificador da fonte para unificação
                'meta'         => [
                    'parcelamento'  => $row['parcelamento'],
                    'titularidade'  => $row['titularidade'],
                    'nome'          => str_contains(strtolower($row['nome']),'newton') ? 'Newton G Costa' : $row['nome'],
                    'tipo_cartao'   => $row['tipo_do_cartao'],
                    'numero_cartao' => ltrim($row['numero_do_cartao'] ?? '', '*'),
                ]
            ];
        })->values(); // O ->values() serve para reindexar a coleção (0, 1, 2, 3...) após filtrarmos as linhas ruins
    }
}