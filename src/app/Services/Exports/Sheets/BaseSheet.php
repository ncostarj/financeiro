<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Comportamento comum às abas "de dados" (Faturas e Lancamentos):
 * cabeçalho em negrito com fundo cinza e auto-size das colunas.
 * A aba Dashboard não estende esta classe porque sua estrutura é
 * bem diferente (fórmulas + gráfico via WithEvents), não uma
 * simples coleção com cabeçalho.
 */
abstract class BaseSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DDDDDD'],
                ],
            ],
        ];
    }
}