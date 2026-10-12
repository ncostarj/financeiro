<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nome' => 'Receitas',
                'tipo' => 'receita',
                'cor'  => '#10B981', // Verde Esmeralda
                'subcategorias' => [
                    [
                        'nome' => 'Rendimentos e Salários',
                        'tipo' => 'receita',
                        'cor'  => '#10B981',
                        'subcategorias' => [
                            ['nome' => 'Remuneração', 'tipo' => 'receita', 'cor' => '#34D399'],
                            ['nome' => 'Férias', 'tipo' => 'receita', 'cor' => '#10B981'],
                            ['nome' => '13º', 'tipo' => 'receita', 'cor' => '#059669'],
                            ['nome' => 'Participação nos Lucros (PLR)', 'tipo' => 'receita', 'cor' => '#047857']
                        ]
                    ],
                    [
                        'nome' => 'Renda Passiva / Investimentos',
                        'tipo' => 'receita',
                        'cor'  => '#10B981',
                        'subcategorias' => [
                            ['nome' => 'Aluguel Recebido', 'tipo' => 'receita', 'cor' => '#10B981'],
                            ['nome' => 'Rendimentos de Investimentos', 'tipo' => 'receita', 'cor' => '#10B981'],
                            ['nome' => 'Dividendos / Fundos Imobiliários', 'tipo' => 'receita', 'cor' => '#10B981'],
                        ]
                    ],
                    [
                        'nome' => 'Outras Receitas',
                        'tipo' => 'receita',
                        'cor'  => '#10B981',
                        'subcategorias' => [
                            ['nome' => 'Cashback', 'tipo' => 'receita', 'cor' => '#10B981'],
                            ['nome' => 'Vendas Avulsas', 'tipo' => 'receita', 'cor' => '#10B981'],
                            ['nome' => 'Reembolsos', 'tipo' => 'receita', 'cor' => '#10B981'],
                            ['nome' => 'Outras', 'tipo' => 'receita', 'cor' => '#10B981'],
                        ]
                    ]
                ]
            ],
            [
                'nome' => 'Despesas',
                'tipo' => 'despesa',
                'cor'  => '#EF4444',
                'subcategorias' => [
                    [
                        'nome' => 'Alimentação',
                        'tipo' => 'despesa',
                        'cor'  => '#EF4444',
                        'subcategorias' => [
                            ['nome' => 'Supermercado', 'tipo' => 'despesa', 'cor' => '#EF4444'],
                            ['nome' => 'Restaurantes', 'tipo' => 'despesa', 'cor' => '#EF4444'],
                            ['nome' => 'Padaria', 'tipo' => 'despesa', 'cor' => '#EF4444'],
                            ['nome' => 'Doceria', 'tipo' => 'despesa', 'cor' => '#EF4444'],
                            ['nome' => 'Lanches', 'tipo' => 'despesa', 'cor' => '#EF4444'],
                            ['nome' => 'iFood', 'tipo' => 'despesa', 'cor' => '#EF4444'],
                            ['nome' => '99', 'tipo' => 'despesa', 'cor' => '#EF4444']
                        ]
                    ],
                    [
                        'nome' => 'Moradia',
                        'tipo' => 'despesa',
                        'cor'  => '#3B82F6', // Azul
                        'subcategorias' => [
                            ['nome' => 'Aluguel', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'Condomínio', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'Água', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'Luz', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'Gás', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'TV', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'Internet', 'tipo' => 'despesa', 'cor' => '#3B82F6'],
                            ['nome' => 'IPTU', 'tipo' => 'despesa', 'cor' => '#3B82F6'], // Movido para cá
                            ['nome' => 'Manutenção da Casa/Apartamento', 'tipo' => 'despesa', 'cor' => '#3B82F6']
                        ]
                    ],
                    [
                        // NOVO CENTRO DE CUSTO: Exclusivo para os custos de possuir um carro
                        'nome' => 'Veículos', 
                        'tipo' => 'despesa',
                        'cor'  => '#F97316', // Laranja
                        'subcategorias' => [
                            ['nome' => 'Combustível', 'tipo' => 'despesa', 'cor' => '#F97316'],
                            ['nome' => 'Seguro Auto', 'tipo' => 'despesa', 'cor' => '#F97316'],
                            ['nome' => 'Manutenção e Peças', 'tipo' => 'despesa', 'cor' => '#F97316'],
                            ['nome' => 'Estacionamento / Pedágio', 'tipo' => 'despesa', 'cor' => '#F97316'],
                            ['nome' => 'IPVA / Licenciamento', 'tipo' => 'despesa', 'cor' => '#F97316'], // Movido para cá
                            ['nome' => 'Lavagem', 'tipo' => 'despesa', 'cor' => '#F97316']
                        ]
                    ],
                    [
                        // CENTRO DE CUSTO AJUSTADO: Exclusivo para mobilidade / deslocamento sem carro próprio
                        'nome' => 'Transporte',
                        'tipo' => 'despesa',
                        'cor'  => '#6366F1', // Indigo
                        'subcategorias' => [
                            ['nome' => 'Uber', 'tipo' => 'despesa', 'cor' => '#6366F1'],
                            ['nome' => '99', 'tipo' => 'despesa', 'cor' => '#6366F1'],
                            ['nome' => 'Transporte Público (Metrô/Ônibus)', 'tipo' => 'despesa', 'cor' => '#6366F1']
                        ]
                    ],
                    [
                        'nome' => 'Saúde e Bem-estar',
                        'tipo' => 'despesa',
                        'cor'  => '#14B8A6', // Verde Água
                        'subcategorias' => [
                            ['nome' => 'Plano de Saúde', 'tipo' => 'despesa', 'cor' => '#14B8A6'],
                            ['nome' => 'Farmácia', 'tipo' => 'despesa', 'cor' => '#14B8A6'],
                            ['nome' => 'Planos de academias', 'tipo' => 'despesa', 'cor' => '#14B8A6'],
                        ]
                    ],
                    [
                        'nome' => 'Compras e Varejo',
                        'tipo' => 'despesa',
                        'cor'  => '#8B5CF6', // Roxo
                        'subcategorias' => [
                            ['nome' => 'Amazon', 'tipo' => 'despesa', 'cor' => '#8B5CF6'],
                            ['nome' => 'Mercado Livre', 'tipo' => 'despesa', 'cor' => '#8B5CF6'],
                            ['nome' => 'Shopee', 'tipo' => 'despesa', 'cor' => '#8B5CF6'],
                            ['nome' => 'Shein', 'tipo' => 'despesa', 'cor' => '#8B5CF6'],
                            ['nome' => 'Lojas Americanas', 'tipo' => 'despesa', 'cor' => '#8B5CF6'],
                            ['nome' => 'Outras Compras', 'tipo' => 'despesa', 'cor' => '#8B5CF6']
                        ]
                    ],
                    [
                        'nome' => 'Educação e Desenvolvimento',
                        'tipo' => 'despesa',
                        'cor'  => '#0EA5E9', // Azul Claro (separado da cor de Compras)
                        'subcategorias' => [
                            ['nome' => 'Camões Pinnochio', 'tipo' => 'despesa', 'cor' => '#0EA5E9'],
                            ['nome' => 'Faculdade / Pós Graduação', 'tipo' => 'despesa', 'cor' => '#0EA5E9'],
                            ['nome' => 'Cursos', 'tipo' => 'despesa', 'cor' => '#0EA5E9'],
                            ['nome' => 'Livros e Materiais', 'tipo' => 'despesa', 'cor' => '#0EA5E9']
                        ]
                    ],
                    [
                        'nome' => 'Lazer, Assinaturas e Viagens',
                        'tipo' => 'despesa',
                        'cor'  => '#EC4899', // Rosa
                        'subcategorias' => [
                            ['nome' => 'Streaming', 'tipo' => 'despesa', 'cor' => '#EC4899'],
                            ['nome' => 'Serviços e Assinaturas', 'tipo' => 'despesa', 'cor' => '#EC4899'],
                            ['nome' => 'Clubes de Pontos / Milhas', 'tipo' => 'despesa', 'cor' => '#EC4899'],
                            ['nome' => 'Passagens Aéreas (GOL/LATAM)', 'tipo' => 'despesa', 'cor' => '#EC4899'], // Viagem é lazer/turismo, não transporte diário
                            ['nome' => 'Viagens, Turismo e Hospedagem', 'tipo' => 'despesa', 'cor' => '#EC4899'],
                            ['nome' => 'Cinema / Teatro', 'tipo' => 'despesa', 'cor' => '#EC4899'],
                            ['nome' => 'Bares e Festas', 'tipo' => 'despesa', 'cor' => '#EC4899']
                        ]
                    ],
                    [
                        'nome' => 'Cuidados Pessoais e Vestuário',
                        'tipo' => 'despesa',
                        'cor'  => '#F472B6', // Rosa Claro
                        'subcategorias' => [
                            ['nome' => 'Roupas e Calçados', 'tipo' => 'despesa', 'cor' => '#F472B6'],
                            ['nome' => 'Salão de Beleza / Barbearia', 'tipo' => 'despesa', 'cor' => '#F472B6'],
                            ['nome' => 'Cosméticos e Perfumaria', 'tipo' => 'despesa', 'cor' => '#F472B6']
                        ]
                    ],
                    [
                        'nome' => 'Família e Presentes',
                        'tipo' => 'despesa',
                        'cor'  => '#FBBF24', // Amarelo
                        'subcategorias' => [
                            ['nome' => 'Presentes', 'tipo' => 'despesa', 'cor' => '#FBBF24'],
                            ['nome' => 'Despesas com Dependentes / Familiares', 'tipo' => 'despesa', 'cor' => '#FBBF24'],
                            ['nome' => 'Doações', 'tipo' => 'despesa', 'cor' => '#FBBF24']
                        ]
                    ],
                    [
                        'nome' => 'Despesas Financeiras',
                        'tipo' => 'despesa',
                        'cor'  => '#64748B', // Cinza Azulado
                        'subcategorias' => [
                            ['nome' => 'Pagamento de Fatura', 'tipo' => 'despesa', 'cor' => '#64748B'],
                            ['nome' => 'Taxas Bancárias', 'tipo' => 'despesa', 'cor' => '#64748B'],
                            ['nome' => 'Juros e Multas', 'tipo' => 'despesa', 'cor' => '#64748B'],
                            ['nome' => 'Imposto de Renda (IR)', 'tipo' => 'despesa', 'cor' => '#64748B'] // IPVA e IPTU foram movidos para os locais corretos
                        ]
                    ],
                    [
                        'nome' => 'Dívidas e Financiamentos',
                        'tipo' => 'despesa',
                        'cor'  => '#991B1B', // Vermelho Escuro
                        'subcategorias' => [
                            ['nome' => 'Financiamento do Imóvel', 'tipo' => 'despesa', 'cor' => '#991B1B'],
                            ['nome' => 'Financiamento do Veículo', 'tipo' => 'despesa', 'cor' => '#991B1B'],
                            ['nome' => 'Empréstimos Pessoais / Consignado', 'tipo' => 'despesa', 'cor' => '#991B1B'],
                            ['nome' => 'Consórcios', 'tipo' => 'despesa', 'cor' => '#991B1B']
                        ]
                    ],
                    [
                        'nome' => 'Outras despesas',
                        'tipo' => 'despesa',
                        'cor'  => '#737373', // Cinza
                        'subcategorias' => [
                            ['nome' => 'Outras', 'tipo' => 'despesa', 'cor' => '#737373'],
                        ]
                    ]
                ]
            ]
        ];

        foreach ($categorias as $categoria) {
            $this->lerSubCategorias($categoria);
        }
    }

    public function lerSubCategorias(array $categoria, ?Categoria $categoriaPai = null): void
    {
        $model = Categoria::updateOrCreate(
            [
                'nome' => $categoria['nome'],
                'tipo' => $categoria['tipo']
            ],
            [
                'cor'              => $categoria['cor'],
                'categoria_pai_id' => $categoriaPai?->id
            ]
        );

        if (!empty($categoria['subcategorias'])) {
            foreach ($categoria['subcategorias'] as $subcategoria) {
                $this->lerSubCategorias($subcategoria, $model);
            }
        }
    }
}