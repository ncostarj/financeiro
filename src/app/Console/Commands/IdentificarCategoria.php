<?php

namespace App\Console\Commands;

use App\Models\Lancamento;
use App\Services\CategoriaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IdentificarCategoria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lancamentos:categorizar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para identificar as categorias dos lancamentos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $lancamentos = Lancamento::orderBy('data_pagamento', 'asc')->get();
        $categoriaService = new CategoriaService();

        $csv = "descricao;categoria;valor;data_vencimento;data_pagamento\n";
        foreach($lancamentos as $lancamento) {
            $categoria = $categoriaService->identificarCategoriaLancamento($lancamento);
            $categoriaNome = $categoria->nome??'--';
            $dataPagamento = $lancamento->data_pagamento ? $lancamento->data_pagamento->format('d/m/Y'):'--';
            $csv .= "{$lancamento->descricao};{$categoriaNome};{$lancamento->valor};{$lancamento->data_vencimento->format('d/m/Y')};{$dataPagamento}\n";
        }

        Storage::put('categorizacao.csv', $csv);

        $this->info('Fim');
    }
}
