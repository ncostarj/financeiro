<?php

namespace App\Exports;

use App\Exports\Sheets\DashboardSheet;
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
            new DashboardSheet(),
        ];
    }
}