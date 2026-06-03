<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LerFatura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financeiro:ler-fatura {tipo=csv} {--a|arquivo=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lê um arquivo de fatura de cartão de credito em csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tipo = $this->argument('tipo');
        $arquivo = $this->option('arquivo');

        if(!$arquivo) {
            $this->fail("Você precisa fornecer um arquivo para ser lido.");
        }

        $file = fopen(storage_path("findocs/$arquivo"), 'r');
        $contador = 0;
        $header = [];
        // dd($this->arquivo);
        $total = 0;

        $parcelas = [];
        $encerrando = [];
        while ($row = fgetcsv($file, 1000, ",")) {
            if ($contador == 0) {
                $header = array_map(fn($item) => Str::slug($item, '_'), $row);
                $contador++;
                continue;
            }
            [$data, $lancamento, $valor] = array_values(array_combine($header, $row));

            if ($lancamento == 'PAGAMENTO EFETUADO') {
                continue;
            }

            preg_match("/(?<numero_parcela>[0-9]{2})\/(?<qtd_parcelas>[0-9]{2})/", $lancamento, $matches);

            if (!empty($matches)) {

                if ($matches['numero_parcela'] != $matches['qtd_parcelas']) {
                    $faltam = (int) $matches['qtd_parcelas'] - (int) $matches['numero_parcela'];
                    $parcelas[] = [
                        'data' => $data,
                        'lancamento' => $lancamento,
                        'valor' => $valor,
                        'numero_parcela' => $matches['numero_parcela'],
                        'qtd_parcelas' => $matches['qtd_parcelas'],
                        'faltam' => $faltam,
                        'valor_restante' => $valor * $faltam
                    ];
                }

                if ($matches['numero_parcela'] == $matches['qtd_parcelas']) {
                    $encerrando[] = [
                        'data' => $data,
                        'lancamento' => $lancamento,
                        'valor' => $valor,
                        'numero_parcela' => $matches['numero_parcela'],
                        'qtd_parcelas' => $matches['qtd_parcelas']
                    ];
                }
            }

            $total += $valor;
            dump("{$data}|{$lancamento}|{$valor}");
        }
        fclose($file);

        $relatorio = fopen(storage_path("relatorios/encerrando.csv"), 'w');

        $csv = "Data;Descricao;Valor;NumeroParcela;QtdParcelas\n";
        $totalEncerrando = 0;
        foreach($encerrando as $encerrado) {
            $totalEncerrando += (float) $encerrado['valor'];
            $csv.="{$encerrado['data']};{$encerrado['lancamento']};{$encerrado['valor']};{$encerrado['numero_parcela']};{$encerrado['qtd_parcelas']}\n";
        }

        fwrite($relatorio, $csv);

        dump($totalEncerrando);
    }
}
