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
    protected $signature = 'faturas:gerenciar {nome?}';

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
        $nomeArquivo = $this->argument('nome');
        $ano = date('Y');
 
        if(empty($nomeArquivo)) {
            $nomeArquivo = "relatorio_financeiro_{$ano}.xlsx";
        }

        // .xlsx é obrigatório aqui: o gráfico e os dropdowns do Dashboard
        // não são suportados no formato binário antigo (.xls).
        if (!str_ends_with($nomeArquivo, '.xlsx')) {
            $nomeArquivo .= '.xlsx';
        }
 
        Excel::store(new RelatorioFinanceiroExport(), $nomeArquivo, 'local');
 
        $this->info("Relatório gerado: storage/app/{$nomeArquivo}");

    }
}
