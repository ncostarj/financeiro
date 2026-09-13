<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LancamentoImportExcel;
use App\Models\Fatura;
use Illuminate\Support\Facades\Storage;

class ImportarFaturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importar:faturas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa todas as faturas existentes dentro do storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Início do script");

        $path = 'xls/itau/faturas';

        foreach (Storage::files($path) as $arquivo) {
            // dd($arquivo);

            $nomeArquivo = basename($arquivo);

            $this->info("Importando {$nomeArquivo}.");

            preg_match('/(?<ano>[0-9]{4})(?<mes>[0-9]{2})(?<dia>[0-9]{2})/', $nomeArquivo, $matches);

            $fatura = Fatura::create([
                'competencia' => "{$matches['dia']}/{$matches['mes']}/{$matches['ano']}"
            ]);

            Excel::import(new LancamentoImportExcel($fatura), $arquivo);

            $totalFatura = $fatura->lancamentos()->sum('valor');

            $fatura->update([
                'total' => $totalFatura
            ]);

            $this->info("{$nomeArquivo} Importado. Total da Fatura desta competencia: R$ {$totalFatura}");

            $this->info("");

        }

        $this->info('FIM');
    }
}
