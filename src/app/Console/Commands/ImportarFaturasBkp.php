<?php

// namespace App\Console\Commands;

// use App\Imports\LancamentoImportBase;
// use App\Imports\LancamentoImportCsv;
// use App\Imports\LancamentoImportExcel;
// use App\Models\Categoria;
// use App\Models\Fatura;
// use App\Models\Lancamento;
// use App\Models\MeioPagamento;
// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Storage;
// use Maatwebsite\Excel\Facades\Excel;
// use RuntimeException;

// class ImportarFaturas extends Command
// {
//     /**
//      * @var string
//      */
//     protected $signature = 'faturas:importara {ano?}';

//     /**
//      * @var string
//      */
//     protected $description = 'Importa todas as faturas existentes dentro do storage';

//     private const MIMES_XLS = [
//         'application/vnd.ms-excel',
//         'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
//         'application/msexcel',
//         'application/excel',
//     ];

//     private const MIMES_CSV = [
//         'text/csv',
//         'text/plain',
//         'application/csv',
//     ];

//     public function handle()
//     {
//         $this->info("Início do script");

//         $ano = $this->argument('ano') ?? date('Y');

//         $paths = [
//             "instituicoes_financeiras/itau/faturas/{$ano}",
//             "instituicoes_financeiras/nubank/faturas/{$ano}",
//         ];

//         foreach ($paths as $path) {

//             foreach (Storage::files($path) as $filePath) {

//                 $arquivo = basename($filePath);
    
//                 preg_match('/\.(xls|xlsx|csv)$/i', $arquivo, $matchesExtensao);
    
//                 if (empty($matchesExtensao)) {
//                     continue;
//                 }
    
//                 $this->info("Importando {$arquivo}.");
    
//                 preg_match('/(?<lancamento>[a-z]+)_(?<meio_pagamento>(itaucard|nubank))_(?<status>(pendente|fechada|pago))_(?<ano>[0-9]{4})(?<mes>[0-9]{2})(?<dia>[0-9]{2})/', $arquivo, $matches);

//                 $meioPagamento = MeioPagamento::where('nome', 'like', "%{$matches['meio_pagamento']}%")->first();
//                 $dataVencimento = "{$matches['ano']}-{$matches['mes']}-{$matches['dia']}";
//                 $dataFechamento = date('Y-m-d', strtotime('-7 days', strtotime("$dataVencimento")));
//                 $status = $matches['status'] == 'pendente' ? 'aberta' : $matches['status'];
    
//                 $fatura = $meioPagamento->faturas()->firstOrCreate(
//                     ['data_vencimento' => $dataVencimento],
//                     [
//                         'data_fechamento' => $dataFechamento,
//                         'data_vencimento' => $dataVencimento,
//                         'status' => $status
//                     ]
//                 );
    
//                 try {
//                     $importador = $this->resolverImportador($filePath, $fatura);
//                 } catch (RuntimeException $e) {
//                     $this->warn($e->getMessage());
//                     continue;
//                 }
    
//                 Excel::import($importador, $filePath);
    
//                 $resumo = [];

//                 // nome_titular agora está direto em lancamentos (sem detalhe)
//                 $lancamentosPorTitular = $fatura->lancamentos()->get()
//                     ->groupBy(fn ($l) => $l->nome_titular ?? '')
//                     ->sortKeys();
    
//                 $totalFamiliaCosta = 0;
//                 foreach ($lancamentosPorTitular as $titular => $lancamentos) {
    
//                     $resumo["total_por_titular"][] = [
//                         'nome' => $titular,
//                         'subtotal' => round($lancamentos->sum('valor'), 2),
//                     ];
    
//                     if ($titular == 'carol' || $titular == 'newton') {
//                         $totalFamiliaCosta += round($lancamentos->sum('valor'), 2);
//                     }
//                 }
    
//                 $resumo["total_por_titular"][] = [
//                     'nome' => 'familia_costa',
//                     'subtotal' => $totalFamiliaCosta,
//                 ];
    
//                 $totalFatura = $fatura->lancamentos->sum('valor');
    
//                 $fatura->update([
//                     'total' => $totalFatura,
//                     'resumo' => $resumo,
//                 ]);
    
//                 $dataVencimentoFormatada = date('d/m/Y', strtotime($dataVencimento));

//                 // Nome dinâmico baseado no meio de pagamento (ex: "Fatura Itaucard" ou "Fatura Nubank")
//                 $descricaoFatura = ucfirst($matches['lancamento']) . ' ' . ucfirst($matches['meio_pagamento']) . " {$dataVencimentoFormatada}";
    
//                 if ($matches['status'] == 'pago') {

//                     $lancamento = new Lancamento();
//                     $lancamento
//                         ->fill([
//                             "descricao"       => $descricaoFatura,
//                             "tipo"            => 'despesa',
//                             "status"          => 'pago',
//                             "origem"          => 'extrato',
//                             "data_vencimento" => $dataVencimento,
//                             "data_pagamento"  => $dataVencimento,
//                             "valor"           => $totalFatura,
//                         ])
//                         ->meioPagamento()->associate(MeioPagamento::where('nome', 'Conta Corrente Itaú Uniclass')->first())
//                         ->categoria()->associate(Categoria::where('nome', 'Pagamento de Fatura')->first())
//                         ->fatura()->associate($fatura)
//                         ->save();
//                 }
    
//                 // $this->info("{$nomeArquivo} Importado. Total da Fatura de {$dataVencimentoFormatada} : R$ {$totalFatura}");
                
//                 $this->info("Importado {$arquivo}. {$descricaoFatura}");
//                 $this->info("");
//             }
//         }

//         $this->info('Fim do script');
//     }

//     /**
//      * Direciona para o importador correto (XLS ou CSV) de acordo com o mime
//      * type real do arquivo, e não apenas pela extensão do nome.
//      */
//     private function resolverImportador(string $filePath, Fatura $fatura): LancamentoImportBase
//     {
//         $mime = Storage::mimeType($filePath);
//         $nomeArquivo = basename($filePath);

//         if (in_array($mime, self::MIMES_XLS, true)) {
//             return new LancamentoImportExcel($nomeArquivo, $fatura);
//         }

//         if (in_array($mime, self::MIMES_CSV, true)) {
//             return new LancamentoImportCsv($nomeArquivo, $fatura);
//         }

//         throw new RuntimeException("Tipo de arquivo não suportado (mime: {$mime}) para {$nomeArquivo}");
//     }
// }
