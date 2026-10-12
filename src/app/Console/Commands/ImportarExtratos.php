<?php

namespace App\Console\Commands;

use App\Imports\ExtratoBradescoImport;
use App\Imports\ExtratoItauImport;
use App\Models\MeioPagamento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportarExtratos extends Command
{
    protected $signature = 'extratos:importar {--ano= : Ano dos extratos (padrão: ano atual)}';

    protected $description = 'Importa extratos de conta corrente do Itaú e Bradesco';

    /**
     * Mapa banco → configuração de importação.
     * Cada entrada define o path no storage, o importador e o nome do MeioPagamento.
     */
    private function configuracoes(string $ano): array
    {
        return [
            [
                'path'            => "instituicoes_financeiras/itau/extratos/{$ano}",
                'importador'      => ExtratoItauImport::class,
                'meio_pagamento'  => 'Conta Corrente Itaú Uniclass',
                'banco'           => 'Itaú',
            ],
            // [
            //     'path'            => "instituicoes_financeiras/bradesco/extratos/{$ano}",
            //     'importador'      => ExtratoBradescoImport::class,
            //     'meio_pagamento'  => 'Conta Corrente Bradesco Prime',
            //     'banco'           => 'Bradesco',
            // ],
        ];
    }

    public function handle(): void
    {
        $ano = $this->option('ano') ?? date('Y');

        $this->info("Importando extratos — ano: {$ano}");
        $this->newLine();

        foreach ($this->configuracoes($ano) as $config) {

            $meioPagamento = MeioPagamento::where('nome', $config['meio_pagamento'])->first();

            if (!$meioPagamento) {
                $this->warn("Meio de pagamento '{$config['meio_pagamento']}' não encontrado. Pulando {$config['banco']}.");
                continue;
            }

            $arquivos = Storage::files($config['path']);

            if (empty($arquivos)) {
                $this->warn("Nenhum arquivo encontrado em: {$config['path']}");
                continue;
            }

            $this->info("[{$config['banco']}] {$meioPagamento->nome}");

            foreach ($arquivos as $arquivo) {

                $nomeArquivo = basename($arquivo);

                // Aceita apenas .xls e .xlsx
                if (!preg_match('/\.(xls|xlsx)$/i', $nomeArquivo)) {
                    continue;
                }

                $this->info("  → Importando {$nomeArquivo}...");

                $importador = new $config['importador']($nomeArquivo, $meioPagamento);

                Excel::import($importador, $arquivo);

                $this->info("  ✓ {$nomeArquivo} importado.");
            }

            $this->newLine();
        }

        $this->info('Importação de extratos concluída.');
    }
}
