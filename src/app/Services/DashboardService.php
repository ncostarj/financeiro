<?php

namespace App\Services;

use App\Models\Lancamento;
use App\Models\MeioPagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Mapa de labels dos meses em português abreviado.
     */
    private array $labelsMeses = [
        1  => 'Jan', 2  => 'Fev', 3  => 'Mar', 4  => 'Abr',
        5  => 'Mai', 6  => 'Jun', 7  => 'Jul', 8  => 'Ago',
        9  => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];

    /**
     * Mapa de titulares: chave -> nome de exibição.
     */
    private array $mapaNomedTitulares = [
        'familia_costa' => 'Família Costa',
        'newton'        => 'Newton',
        'carol'         => 'Carol',
        'marli'         => 'Marli',
        'marilene'      => 'Marilene',
    ];

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Aplica o filtro de titular a uma query de Lancamento.
     * Para 'familia_costa' inclui newton+carol e também lançamentos de extrato sem titular.
     * Para outros valores, filtra exatamente pelo nome_titular.
     */
    private function aplicarFiltroTitular($query, string $titular)
    {
        if ($titular === 'familia_costa') {
            $query->where(function ($q) {
                $q->whereIn('nome_titular', ['newton', 'carol'])
                  ->orWhere(function ($q2) {
                      // extrato sem titular pertence ao contexto família
                      $q2->where('origem', 'extrato')
                         ->whereNull('nome_titular');
                  });
            });
        } else {
            $query->where('nome_titular', $titular);
        }

        return $query;
    }

    /**
     * Retorna a soma de valor para um tipo (receita|despesa) em um determinado mês/ano e titular.
     */
    private function somaDoMes(int $mes, int $ano, string $tipo, string $titular): float
    {
        $query = Lancamento::query()
            ->where('tipo', $tipo)
            ->whereYear('data_vencimento', $ano)
            ->whereMonth('data_vencimento', $mes);

        $this->aplicarFiltroTitular($query, $titular);

        return (float) ($query->sum('valor') ?? 0);
    }

    /**
     * Calcula a variação percentual entre dois valores.
     * Retorna 0 quando o valor anterior é zero (evita divisão por zero).
     */
    private function variacaoPercentual(float $atual, float $anterior): float
    {
        if ($anterior == 0) {
            return 0.0;
        }

        return (($atual - $anterior) / $anterior) * 100;
    }

    // -------------------------------------------------------------------------
    // Métodos públicos
    // -------------------------------------------------------------------------

    /**
     * Retorna o resumo financeiro do mês: totais de despesas e receitas, saldo
     * e variação percentual em relação ao mês anterior.
     */
    public function resumoMes(int $mes, int $ano, string $titular): array
    {
        $despesasAtual = $this->somaDoMes($mes, $ano, 'despesa', $titular);
        $receitasAtual = $this->somaDoMes($mes, $ano, 'receita', $titular);

        // Calcular mês anterior
        $dataAnterior     = Carbon::createFromDate($ano, $mes, 1)->subMonth();
        $mesAnterior      = (int) $dataAnterior->month;
        $anoAnterior      = (int) $dataAnterior->year;

        $despesasAnterior = $this->somaDoMes($mesAnterior, $anoAnterior, 'despesa', $titular);
        $receitasAnterior = $this->somaDoMes($mesAnterior, $anoAnterior, 'receita', $titular);

        return [
            'total_despesas'    => $despesasAtual,
            'total_receitas'    => $receitasAtual,
            'saldo'             => $receitasAtual - $despesasAtual,
            'variacao_despesas' => $this->variacaoPercentual($despesasAtual, $despesasAnterior),
            'variacao_receitas' => $this->variacaoPercentual($receitasAtual, $receitasAnterior),
        ];
    }

    /**
     * Retorna os gastos por meio de pagamento (cartões e contas correntes) no mês.
     */
    public function gastosPorMeioPagamento(int $mes, int $ano, string $titular): array
    {
        $query = Lancamento::query()
            ->select(
                'meios_pagamentos.nome as nome',
                'meios_pagamentos.tipo as tipo_meio',
                DB::raw('SUM(lancamentos.valor) as total')
            )
            ->join('meios_pagamentos', 'meios_pagamentos.id', '=', 'lancamentos.meio_pagamento_id')
            ->where('lancamentos.tipo', 'despesa')
            ->whereYear('lancamentos.data_vencimento', $ano)
            ->whereMonth('lancamentos.data_vencimento', $mes)
            ->groupBy('lancamentos.meio_pagamento_id', 'meios_pagamentos.nome', 'meios_pagamentos.tipo')
            ->orderByDesc('total');

        $this->aplicarFiltroTitular($query, $titular);

        $resultados = $query->get();

        $cartoes = [];
        $contas  = [];

        foreach ($resultados as $row) {
            $item = [
                'nome'  => $row->nome,
                'total' => (float) $row->total,
            ];

            // Cartões: tipo que contém 'cartao' (ex: cartao_credito, cartao_debito)
            if (str_contains($row->tipo_meio, 'cartao')) {
                $cartoes[] = $item;
            } elseif ($row->tipo_meio === 'conta_corrente') {
                $contas[] = $item;
            }
        }

        return [
            'cartoes' => $cartoes,
            'contas'  => $contas,
        ];
    }

    /**
     * Retorna os gastos por categoria no mês (top 8), com percentual sobre o total.
     */
    public function gastosPorCategoria(int $mes, int $ano, string $titular): array
    {
        $query = Lancamento::query()
            ->select(
                DB::raw("COALESCE(categorias.nome, 'Sem Categoria') as nome"),
                DB::raw("COALESCE(categorias.cor, '#9ca3af') as cor"),
                DB::raw('SUM(lancamentos.valor) as total')
            )
            ->leftJoin('categorias', 'categorias.id', '=', 'lancamentos.categoria_id')
            ->where('lancamentos.tipo', 'despesa')
            ->whereYear('lancamentos.data_vencimento', $ano)
            ->whereMonth('lancamentos.data_vencimento', $mes)
            ->groupBy('lancamentos.categoria_id', 'categorias.nome', 'categorias.cor')
            ->orderByDesc('total')
            ->limit(8);

        $this->aplicarFiltroTitular($query, $titular);

        $resultados = $query->get();

        $totalGeral = $resultados->sum('total');

        return $resultados->map(function ($row) use ($totalGeral) {
            return [
                'nome'       => $row->nome,
                'cor'        => $row->cor,
                'total'      => (float) $row->total,
                'percentual' => $totalGeral > 0
                    ? round(((float) $row->total / (float) $totalGeral) * 100, 2)
                    : 0.0,
            ];
        })->toArray();
    }

    /**
     * Retorna o comparativo mensal dos últimos 6 meses a partir do mês atual do ano informado,
     * com totais de despesas e receitas por mês.
     */
    public function comparativoMensal(int $ano, string $titular): array
    {
        // Se o ano informado for o ano atual, usa o mês corrente; senão usa dezembro
        $mesAtual = Carbon::now()->year === $ano ? Carbon::now()->month : 12;

        $periodos = [];
        for ($i = 5; $i >= 0; $i--) {
            $data       = Carbon::createFromDate($ano, $mesAtual, 1)->subMonths($i);
            $periodos[] = ['mes' => (int) $data->month, 'ano' => (int) $data->year];
        }

        $resultado = [];

        foreach ($periodos as $periodo) {
            $despesas   = $this->somaDoMes($periodo['mes'], $periodo['ano'], 'despesa', $titular);
            $receitas   = $this->somaDoMes($periodo['mes'], $periodo['ano'], 'receita', $titular);

            $resultado[] = [
                'mes'      => $periodo['mes'],
                'ano'      => $periodo['ano'],
                'label'    => $this->labelsMeses[$periodo['mes']],
                'despesas' => $despesas,
                'receitas' => $receitas,
            ];
        }

        return $resultado;
    }

    /**
     * Retorna os gastos por titular no mês (sem filtro de titular).
     * Inclui um registro agregado para 'familia_costa' (newton + carol).
     */
    public function gastosPorTitular(int $mes, int $ano): array
    {
        // Buscar despesas de todos os titulares individuais no período
        $rows = Lancamento::query()
            ->select(
                'nome_titular',
                DB::raw('SUM(valor) as total')
            )
            ->where('tipo', 'despesa')
            ->whereYear('data_vencimento', $ano)
            ->whereMonth('data_vencimento', $mes)
            ->whereNotNull('nome_titular')
            ->groupBy('nome_titular')
            ->get()
            ->pluck('total', 'nome_titular')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // Total geral (base para os percentuais)
        $totalGeral = array_sum(array_values($rows));

        $itens = [];

        // Família Costa = soma de newton + carol
        $despesasFamilia = ($rows['newton'] ?? 0.0) + ($rows['carol'] ?? 0.0);
        $itens[] = [
            'nome'       => 'Família Costa',
            'chave'      => 'familia_costa',
            'despesas'   => $despesasFamilia,
            'percentual' => $totalGeral > 0
                ? round(($despesasFamilia / $totalGeral) * 100, 2)
                : 0.0,
        ];

        // Titulares individuais
        foreach (['newton', 'carol', 'marli', 'marilene'] as $chave) {
            $despesas = $rows[$chave] ?? 0.0;
            $itens[]  = [
                'nome'       => $this->mapaNomedTitulares[$chave],
                'chave'      => $chave,
                'despesas'   => $despesas,
                'percentual' => $totalGeral > 0
                    ? round(($despesas / $totalGeral) * 100, 2)
                    : 0.0,
            ];
        }

        // Ordenar por despesas DESC
        usort($itens, fn ($a, $b) => $b['despesas'] <=> $a['despesas']);

        return $itens;
    }
}
