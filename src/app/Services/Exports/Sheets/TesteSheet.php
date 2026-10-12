<?php

namespace App\Exports\Sheets;

use App\Models\Categoria;
use App\Models\Fatura;
use App\Models\Lancamento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LancamentosPorCategoriaSheet implements FromCollection, WithHeadings, WithTitle
{
    private Collection $faturas;
    private array $categorias = [];
    private array $mesesHeading = [];

    public function __construct()
    {
        $this->faturas = Fatura::with(['lancamentos', 'lancamentos.categoria'])
            ->where('status', 'pago')
            ->get();

        foreach ($this->faturas as $fatura) {
            $this->mesesHeading[] = $fatura->data_vencimento->format('m/Y');

            foreach ($fatura->lancamentos as $lancamento) {
                $categoria = $lancamento->categoria?->nome ?? 'Sem Categoria';
                $this->categorias[$categoria] = 1;
            }
        }

        // Garante que não teremos meses duplicados caso existam 2 faturas pagas no mesmo mês
        $this->mesesHeading = array_values(array_unique($this->mesesHeading));
        
        $this->categorias = array_keys($this->categorias);
        sort($this->categorias);
    }

    public function title(): string
    {
        return 'Lançamentos por Categoria';
    }

    public function headings(): array
    {
        return [
            ['Mês Selecionado:', 'Total Geral'], // Linha 1 (B1 inicia como Total Geral)
            [],                                  // Linha 2 (Espaçamento)
            array_merge(
                ['Categoria'],
                $this->mesesHeading,
                ['Total Geral', 'Categoria Porcentagem', 'Valor Exibido']
            ),
        ];
    }

    public function collection()
    {
        // 1. Prepara o array base usando o nome da categoria como chave
        $linhas = [];

        foreach ($this->categorias as $categoria) {
            $linha = ['categoria' => $categoria];
            
            foreach ($this->mesesHeading as $mes) {
                $linha[$mes] = 0;
            }
            
            $linha['subtotal_categoria'] = 0;
            $linha['porcentagem_categoria'] = "{$categoria} - 0%";
            $linha['valor_exibido'] = ""; 
            
            $linhas[$categoria] = $linha;
        }

        // 2. Itera sobre as faturas e popula os valores
        foreach ($this->faturas as $fatura) {
            
            // O mês da coluna vai bater com o format('m/Y') gerado no construtor
            $mes = $fatura->data_vencimento->format('m/Y');

            $lancamentos = $fatura->lancamentos
                ->where('descricao', '!=', 'Pagamento de Fatura')
                ->groupBy(fn($l) => $l->categoria?->nome ?? 'Sem Categoria');

            foreach ($lancamentos as $categoria => $lancamentosCategoria) {
                
                // Como você já agrupou, basta somar direto da collection!
                $subtotalCategoria = $lancamentosCategoria->sum('valor');
                
                // Se a categoria existe nas linhas, incrementa os totais
                if (isset($linhas[$categoria])) {
                    $linhas[$categoria][$mes] += $subtotalCategoria;
                    $linhas[$categoria]['subtotal_categoria'] += $subtotalCategoria;
                }
            }
        }

        dd($linhas);

        // 3. Converte de volta para Collection perdendo as chaves nomeadas 
        // para que o Excel renderize linha por linha corretamente
        return collect(array_values($linhas));
    }
}