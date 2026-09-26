<?php

namespace App\Console\Commands;

use App\Imports\LancamentoImportBase;
use App\Imports\LancamentoImportCsv;
use App\Imports\LancamentoImportExcel;
use App\Models\Categoria;
use App\Models\Fatura;
use App\Models\Lancamento;
use App\Models\MeioPagamento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class ImportarFaturas extends Command
{
    /**
     * @var string
     */
    protected $signature = 'faturas:importar';

    /**
     * @var string
     */
    protected $description = 'Importa todas as faturas existentes dentro do storage';

    private const MIMES_XLS = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msexcel',
        'application/excel',
    ];

    private const MIMES_CSV = [
        'text/csv',
        'text/plain',
        'application/csv',
    ];

    public function handle()
    {
        $this->info("Início do script");

        $ano = '2026';

        $paths = [
            "instituicoes_financeiras/itau/faturas/{$ano}/xls",
            "instituicoes_financeiras/nubank/faturas/{$ano}/csv",
        ];

        foreach ($paths as $path) {

            foreach (Storage::files($path) as $arquivo) {
    
                $nomeArquivo = basename($arquivo);
    
                preg_match('/\.(xls|xlsx|csv)$/i', $nomeArquivo, $matchesExtensao);
    
                if (empty($matchesExtensao)) {
                    continue;
                }
    
                $this->info("Importando {$nomeArquivo}.");
    
                preg_match('/(?<meio_pagamento>(itaucard|nubank))_(?<status>(pendente|fechada|pago))_(?<ano>[0-9]{4})(?<mes>[0-9]{2})(?<dia>[0-9]{2})/', $nomeArquivo, $matches);
    
                $dataFechamento = "{$matches['ano']}-{$matches['mes']}-02";
                $dataVencimento = "{$matches['ano']}-{$matches['mes']}-{$matches['dia']}";
    
                $meioPagamento = MeioPagamento::where('nome', 'like', "%{$matches['meio_pagamento']}%")->first();
                $status = $matches['status'] == 'pendente' ? 'aberta' : $matches['status'];
    
                $fatura = $meioPagamento->faturas()->firstOrCreate(
                    ['data_vencimento' => $dataVencimento],
                    [
                        'data_fechamento' => $dataFechamento,
                        'data_vencimento' => $dataVencimento,
                        'status' => $status
                    ]
                );
    
                try {
                    $importador = $this->resolverImportador($arquivo, $fatura);
                } catch (RuntimeException $e) {
                    $this->warn($e->getMessage());
                    continue;
                }
    
                Excel::import($importador, $arquivo);
    
                $resumo = [];
    
                $lancamentosPorTitular = $fatura->lancamentos->groupBy('nome_titular')->sortKeys();
    
                $totalFamiliaCosta = 0;
                foreach ($lancamentosPorTitular as $titular => $lancamentos) {
    
                    $resumo["total_por_titular"][] = [
                        'nome' => $titular,
                        'subtotal' => round($lancamentos->sum('valor'), 2),
                    ];
    
                    if ($titular == 'carol' || $titular == 'newton') {
                        $totalFamiliaCosta += round($lancamentos->sum('valor'), 2);
                    }
                }
    
                $resumo["total_por_titular"][] = [
                    'nome' => 'familia_costa',
                    'subtotal' => $totalFamiliaCosta,
                ];
    
                $totalFatura = $fatura->lancamentos->sum('valor');
    
                $fatura->update([
                    'total' => $totalFatura,
                    'resumo' => $resumo,
                ]);
    
                $dataVencimentoFormatada = date('d/m/Y', strtotime($dataVencimento));
    
                if ($matches['status'] == 'pago') {
                    $lancamento = new Lancamento();
                    $lancamento
                        ->fill([
                            "descricao"       => "Fatura Itaucard {$dataVencimentoFormatada}",
                            "tipo"            => 'despesa',
                            "status"          => 'pago',
                            "data_vencimento" => $dataVencimento,
                            "data_pagamento"  => $dataVencimento,
                            "valor"           => $totalFatura,
                        ])
                        ->meioPagamento()->associate(MeioPagamento::where('nome', 'Conta Corrente Itaú Uniclass')->first())
                        ->categoria()->associate(Categoria::where('nome', 'Pagamento de Fatura')->first())
                        ->save();
                }
    
                $this->info("{$nomeArquivo} Importado. Total da Fatura de {$dataVencimentoFormatada} : R$ {$totalFatura}");
    
                $this->info("");
            }
        }


        // $this->info('FIM');
    }

    /**
     * Direciona para o importador correto (XLS ou CSV) de acordo com o mime
     * type real do arquivo, e não apenas pela extensão do nome.
     */
    private function resolverImportador(string $arquivo, Fatura $fatura): LancamentoImportBase
    {
        $mime = Storage::mimeType($arquivo);

        if (in_array($mime, self::MIMES_XLS, true)) {
            return new LancamentoImportExcel($fatura);
        }

        if (in_array($mime, self::MIMES_CSV, true)) {
            return new LancamentoImportCsv($fatura);
        }

        throw new RuntimeException("Tipo de arquivo não suportado (mime: {$mime}) para {$arquivo}");
    }
}
