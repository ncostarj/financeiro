<?php

namespace App\Exports; // Dica: A convenção do Laravel Excel é usar Exports e não Imports para gerar relatórios

use App\Exports\Sheets\FaturasSheet;
use App\Exports\Sheets\LancamentosSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RelatorioFinanceiroExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new FaturasSheet(),
            new LancamentosSheet(),
        ];
    }
}