<?php

namespace App\Console\Commands;

use App\Exports\RelatorioFinanceiroExport;
use App\Models\Fatura;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GerenciarFaturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faturas:gerenciar';

    //  {?data_vencimento}

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command para gerenciar, visualizar e testar os dados importados pelos xls';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Excel::store(new RelatorioFinanceiroExport, 'relatorio_financeiro.xlsx');

    }
}
