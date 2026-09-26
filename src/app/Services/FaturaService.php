<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FaturaService {

//     private Collection $lancamentos;
//     private Collection $lancamentosEncerrando;

//     public function __construct(Collection $lancamentos) {
//         $this->lancamentos = $lancamentos->map(function($lancamento){
//             if($lancamento['nome'] == 'Newton G Costa') {
//                 $lancamento['nome'] = 'Newton Gonzaga Costa';
//             }

//             return $lancamento;
//         });
//     }

//     public function processarLancamentos() {
// // preg_match("/(?<numero_parcela>[0-9]{2})\/(?<qtd_parcelas>[0-9]{2})/", $lancamento, $matches);

// //             if (!empty($matches)) {

// //                 if ($matches['numero_parcela'] != $matches['qtd_parcelas']) {
// //                     $faltam = (int) $matches['qtd_parcelas'] - (int) $matches['numero_parcela'];
// //                     $lancamentos->add([
// //                         'data' => $data,
// //                         'lancamento' => $lancamento,
// //                         'valor' => $valor,
// //                         'numero_parcela' => $matches['numero_parcela'],
// //                         'qtd_parcelas' => $matches['qtd_parcelas'],
// //                         'faltam' => $faltam,
// //                         'valor_restante' => $valor * $faltam
// //                     ]);
// //                 }

// //             }

//         foreach($this->lancamentos->where('lancamento','!=', 'Pagamento Efetuado')->groupBy('nome') as $titular => $lancamentos) {
//             dump("{$titular}",$lancamentos->sum('valor'));
//             // $totalAmazon = 0;
//             // foreach($lancamentos as $lancamento) {
//             //     if(str_contains($lancamento['lancamento'],'Amazon')) {
//             //         $totalAmazon += $lancamento['valor'];
//             //     }
//             // }
//             // dump($totalAmazon);
//         }

// // ->where('lancamento', '!=', 'Pagamento Efetuado')
//     }

//     //  if ($matches['numero_parcela'] == $matches['qtd_parcelas']) {
//     //                 $this->encerrando[] = [
//     //                     'data' => $data,
//     //                     'lancamento' => $lancamento,
//     //                     'valor' => $valor,
//     //                     'numero_parcela' => $matches['numero_parcela'],
//     //                     'qtd_parcelas' => $matches['qtd_parcelas']
//     //                 ];
//     //             }

//     //                     $relatorio = fopen(storage_path("relatorios/encerrando.csv"), 'w');

//     //     $csv = "Data;Descricao;Valor;NumeroParcela;QtdParcelas\n";
//     //     $totalEncerrando = 0;
//     //     foreach ($this->encerrando as $encerrado) {
//     //         $totalEncerrando += (float) $encerrado['valor'];
//     //         $csv .= "{$encerrado['data']};{$encerrado['lancamento']};{$encerrado['valor']};{$encerrado['numero_parcela']};{$encerrado['qtd_parcelas']}\n";
//     //     }

//     //     fwrite($relatorio, $csv);

//     //     dump($totalEncerrando);
}