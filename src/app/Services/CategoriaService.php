<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Lancamento;

class CategoriaService
{
    public function identificarCategoriaLancamento(Lancamento $lancamento): ?Categoria
    {
        $descricao = strtolower($lancamento->descricao);

        // DICIONÁRIO DE REGEX COM ADERÊNCIA ÀS SUBCATEGORIAS DO SEEDER
        // A ORDEM IMPORTA: Regras que contêm exceções ou palavras compostas (ex: amazonprimebr) 
        // devem vir ANTES das regras genéricas (ex: amazon) para serem avaliadas primeiro.
        $regras = [
            // EDUCAÇÃO E DESENVOLVIMENTO
            'Camões Pinnochio'                 => '/(camoes|\*isaac)/i',
            'Faculdade / Pós Graduação'        => '/(estacio|unyleya|faculdade)/i',
            'Cursos'                           => '/(kiwify|curso)/i',

            // LAZER, ASSINATURAS E VIAGENS
            'Cinema'                           => '/(cines via parque)/i',
            'Streaming'                        => '/(globoplay|netflix|primevideo|appletv|disney|hbomax|spotify|telecine)/i',
            'Clubes de Pontos / Milhas'        => '/(smiles|livelo|latampass|tudoazul)/i',
            // Adicionado 'primebr' e 'amazonprime' aqui. Como vem antes de 'Amazon', vai ser pego corretamente!
            'Serviços e Assinaturas'           => '/(google\s*one|tagitau|sams|fing|meli|primebr|amazonprime|produtos globo)/i',
            'Passagens Aéreas (GOL/LATAM)'     => '/(gol|latam|voegol|azul\s*linhas)/i',
            'Viagens, Turismo e Hospedagem'    => '/(booking|loumar|hotel|pousada|resort|decolar)/i',

            // SAÚDE E BEM-ESTAR
            'Planos de academias'              => '/(totalpass|wellhub|smartfit|bodytech)/i',
            'Farmácia'                         => '/(drogaria|drogas|venancio|pacheco|raia$|drogasmil|farmalife|farmacia)/i',

            // COMPRAS E VAREJO
            // Só vai cair em 'Amazon' se não tiver dado 'match' em 'Serviços e Assinaturas' lá em cima
            'Amazon'                           => '/(amazon)/i',
            'Mercado Livre'                    => '/(mercadolivre)/i',
            'Shopee'                           => '/(shopee)/i',
            'Shein'                            => '/(shein)/i',
            'Compras e Varejo'                 => '/(aliexpress|magalu|americanas|lojas americ|amigao|lilipe)/i', 

            // ALIMENTAÇÃO
            'Supermercado'                     => '/(supermercad|vianense|guanabara|guanab|atacadao|prezunic|assai|megabox|casafruti|horti|hortifruti|casas pedro|supermarket|sendas)/i',
            'Delivery'                         => '/(ifd\*|ifood|99food|ze\s*delivery)/i',
            'Fast Food'                        => '/(mc\s*donald|mcdonald|burger|bobs|subway)/i',
            'Padaria'                          => '/(panificadora|padaria)/i',
            'Restaurantes'                     => '/(mocellin|rio\s*brasa|outback|restaurante|churrascaria|oliva|gallo da vil|parme|bauru gourme|pizzaria|madero)/i',
            'Lanches'                          => '/(hortela|kopenhagen|cacau|alzira|salgados)/i',

            // MORADIA
            'Condomínio'                       => '/(grupamento resi|condominio|condomi)/i',
            'Energia Elétrica'                 => '/light/i',
            'IPTU'                             => '/iptu/i',
            'Gás'                              => '/(companhia\s+distribui|ceg)/i',

            // VEÍCULOS (Custos de posse do carro)
            'Combustível'                      => '/(posto|petrobras|ipiranga|shell)/i',
            'Seguro Auto'                      => '/(porto\s*seguro|bradesco\s*seguros|azul\s*seguros|tokio\s*marine|sulamerica)/i',
            'Manutenção e Peças'               => '/(toyota|hyundai|oficina|autocenter)/i',
            'Manutenção da casa'               => '/(fluzao)/i',
            'IPVA / Licenciamento'             => '/(gringo|detran|ipva)/i',

            // TRANSPORTE DIÁRIO (Mobilidade)
            'Uber'                             => '/(uber|uber_credit|99app)/i',
            '99'                               => '/(99app|99\s*ride)/i',

            // CUIDADOS PESSOAIS E VESTUÁRIO
            'Roupas e Calçados'                => '/(south|riachuelo|daluz|adidas|nike|cea|lojasriachue|botanope|calcados son|centauro|moda|havaianas)/i',

            // DESPESAS FINANCEIRAS
            'Taxas Bancárias'                  => '/(iof|anuidade diferenci|encargos)/i',
            'Juros e Multas'                   => '/(juros pagamento contas|atraso|atrasada|valor pendente)/i',
        ];

        $descricao = mb_strtolower($lancamento->descricao, 'UTF-8');

        // PROCESSAMENTO E BUSCA
        foreach ($regras as $categoriaNome => $regex) {
            if (preg_match($regex, $descricao)) {
                return $this->buscarCategoria($categoriaNome);
            }
        }

        return null;
    }

    private function buscarCategoria(string $nome): ?Categoria
    {
        return Categoria::where('nome', 'like', "%{$nome}%")->first();
    }
}