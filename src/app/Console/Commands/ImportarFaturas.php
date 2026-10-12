<?php

namespace App\Console\Commands;

use App\Imports\LancamentoImportBase;
use App\Imports\LancamentoImportCsv;
use App\Imports\LancamentoImportExcel;
use App\Models\Categoria;
use App\Models\Fatura;
use App\Models\Lancamento;
use App\Models\MeioPagamento;
use App\Services\CategoriaService;
use App\Services\Imports\FaturaCsvImport;
use App\Services\Imports\FaturaImportBase;
use App\Services\Imports\FaturaXlsImport;
use App\Services\Imports\FaturaXlsImportBase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class ImportarFaturas extends Command
{
    /**
     * @var string
     */
    protected $signature = 'faturas:importar {ano?}';

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

    private function getImportador(array $fatura, string $arquivo): FaturaImportBase
    {

        $mime = Storage::mimeType("{$fatura['path']}/{$arquivo}");

        if (in_array($mime, self::MIMES_XLS)) {
            return new FaturaXlsImport();
        }

        if (in_array($mime, self::MIMES_CSV)) {
            return new FaturaCsvImport($fatura['delimiter']);
        }

        throw new RuntimeException("Tipo de arquivo não suportado (mime: {$mime}) para {$arquivo}");
    }

    public function handle()
    {
        $this->info("Início do script");

        $ano = $this->argument('ano') ?? date('Y');

        $faturas = [
            [
                'path' => "instituicoes_financeiras/itau/faturas/{$ano}",
            ],
            [
                'path' => "instituicoes_financeiras/nubank/faturas/{$ano}",
                'delimiter' => ','
            ],
        ];

        // Coleção master que vai unificar Itaú e Nubank
        $lancamentos = collect();

        foreach ($faturas as $fatura) {
            foreach (Storage::files($fatura['path']) as $filePath) {
                $arquivo = basename($filePath);

                // Instancia o importador específico
                $importador = $this->getImportador($fatura, $arquivo);

                // Executa o import oficial (dispara o método collection() internamente)
                Excel::import($importador, $filePath);

                // Recupera os dados guardados na propriedade interna da classe importadora
                $dadosDesteArquivo = $importador->getDadosNormalizados();

                // Unifica na coleção principal do comando
                $lancamentos = $lancamentos->merge($dadosDesteArquivo);
            }
        }

        $categoriaService = new CategoriaService();

        $contadorCategoria = [
            'categorizado' => 0,
            'nao_categorizado' => 0,
            'total' => 0
        ];

        $csv = "data;descricao;valor;origem;categoria\n";

        foreach($lancamentos as $lancamento) {

            if(str_contains(strtolower($lancamento['descricao']),'pagamento')) { continue; }

            $categoria = $categoriaService->identificarCategoriaLancamento($lancamento['descricao']);
            $lancamento['categoria'] = $categoria ? $categoria->nome : 'Sem categoria';

            $lancamento['data_formatada'] = implode('/', array_reverse(explode('-',$lancamento['data'])));
            $lancamento['valor_formatado'] = 'R$ ' . number_format($lancamento['valor'],2,',','.kf');

            if($lancamento['categoria'] != 'Sem categoria') $contadorCategoria['categorizado']++;
            if($lancamento['categoria'] == 'Sem categoria') $contadorCategoria['nao_categorizado']++;

            $contadorCategoria['total']++;

            $csv .= "{$lancamento['data_formatada']};{$lancamento['descricao']};{$lancamento['valor_formatado']};{$lancamento['origem']};{$lancamento['categoria']}\n";
        }

        Storage::put('csv/lancamentos.csv', $csv);

        $this->info("total|categorizado|nao_categorizado");
        $this->info("{$contadorCategoria['total']}|{$contadorCategoria['categorizado']}|{$contadorCategoria['nao_categorizado']}");

        $this->info('Fim do script');
    }
}
