<?php

namespace App\Services\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class FaturaCsvImport extends FaturaImportBase implements WithHeadingRow, SkipsEmptyRows, ToCollection, WithCustomCsvSettings
{
    private string $delimiter;

    public function __construct(string $delimiter)
    {
        parent::__construct(); // Inicializa a collection na classe base
        $this->delimiter = $delimiter;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter'      => $this->delimiter,
            'enclosure'      => '"',
            'input_encoding' => 'UTF-8',
        ];
    }

    public function collection(Collection $rows): void
    {
        // Mapeia para a MESMA estrutura unificada do Itaú/XLS
        $this->dadosNormalizados = $rows->map(function ($row) {

            preg_match('/(?<parcelamento>Parcela [0-9]+\/[0-9]+)/', $row['title'], $matches);

            return [
                'data'      => $row['date'],       // Certifique-se de formatar se necessário (Y-m-d)
                'descricao' => trim($row['title']),
                'valor'     => (float) $row['amount'],
                'origem'    => 'Nubank',           // Identificador da fonte
                'meta'      => [
                    'parcelamento'  => $matches['parcelamento']??'',
                    'titularidade'  => 'Titular',
                    'nome'          => 'Newton(Marilene)',
                    'tipo_cartao'   => 'Físico',
                    'numero_cartao' => '',
                ]
            ];
        });
    }
}
