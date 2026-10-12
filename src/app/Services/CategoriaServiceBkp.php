<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Lancamento;

class CategoriaServicea

{
    // public function identificarCategoriaLancamento(Lancamento $lancamento): ?Categoria
    public function identificarCategoriaLancamento(string $descricao): ?Categoria
    {
        $descricao = strtolower($descricao);

        // DICIONÁRIO DE REGEX COM ADERÊNCIA ÀS SUBCATEGORIAS DO SEEDER
        // A ORDEM IMPORTA: Regras que contêm exceções ou palavras compostas (ex: amazonprimebr) 
        // devem vir ANTES das regras genéricas (ex: amazon) para serem avaliadas primeiro.
        $regras = [
            // ================================================================
            // EXTRATOS DE CONTA CORRENTE
            // Estas regras devem vir PRIMEIRO pois os padrões de extrato são
            // mais específicos e não conflitam com nomes de estabelecimentos.
            // ================================================================

            // Pagamento de fatura do cartão (Itaú extrato)
            'Pagamento de Fatura'              => '/(fatura paga|pagamento fatura|pagto fatura)/i',

            // // Rendimento de aplicação automática (Itaú — "REND PAGO APLIC AUT MAIS")
            // 'Rendimentos de Investimentos'     => '/(rend pago aplic|rend\.\s*aplic|rendimento)/i',

            // // Transferências PIX recebidas (crédito na conta) — Itaú e Bradesco
            // // "CREDITO LIBERAD PIX", "Pix Recebido"
            // 'Outras Receitas'                  => '/(credito liberad|pix recebido|pix\s+receb)/i',

            // // Transferências PIX enviadas — Itaú: "PIX TRANSF <nome>", Bradesco: "Pix Enviado"
            // // TED 237 = transferência para Bradesco
            // 'Despesas com Dependentes / Familiares' => '/(pix\s+transf|pix\s+enviado|ted\s+\d)/i',

            // // Pagamentos QR Code (Itaú — "PIX QRS <destino>", "PIX CARTAO <destino>")
            // // Destinos específicos têm regras próprias abaixo (CEG, TIM, SEFAZ); essa é o fallback
            // 'Outras despesas'                  => '/(pix\s+qrs|pix\s+cartao|pix\s+origem\s+cartao)/i',

            // // Débito automático via INT (Itaú) e pagamentos QR para destinos específicos
            // 'Gás'                              => '/(int\s*\/\s*ceg|pix\s+(qrs|cartao)\s*\*?\s*ceg)/i',
            // 'Internet'                         => '/(int\s*\/\s*tim|da\s+tim\s+celu|int\s*\/\s*claro|int\s*\/\s*vivo|int\s*\/\s*oi\b)/i',
            // 'IPTU'                             => '/(int\s*\/\s*sefaz|pgto\s+elet\s+trib|sefaz)/i',

            // // Saques em caixa eletrônico
            // 'Outras despesas'                  => '/(saque\s+din|saque\s+atm|saque\s+cartao)/i',

            // // Taxas e encargos bancários (Bradesco e Itaú)
            // 'Taxas Bancárias'                  => '/(enc\s+lim|tarifa\s+banc|cesta\s+classic|iof\s+util|pac\s+servico)/i',
            // 'Juros e Multas'                   => '/(juros\s+limite|encargo\s+-\s+\d)/i',

            // // Parcela de crédito pessoal (Bradesco — "Parc Cred Pess", "Contr XXXXXXX Parc")
            // 'Empréstimos Pessoais / Consignado' => '/(parc\s+cred\s+pess|contr\s+\d+\s+parc)/i',

            // // ================================================================
            // // FATURAS DE CARTÃO DE CRÉDITO
            // // ================================================================

            // // EDUCAÇÃO E DESENVOLVIMENTO
            // 'Camões Pinnochio'                 => '/(camoes|\*isaac)/i',
            // 'Faculdade / Pós Graduação'        => '/(estacio|unyleya|faculdade)/i',
            // 'Cursos'                           => '/(kiwify|curso)/i',

            // // LAZER, ASSINATURAS E VIAGENS
            // 'Cinema'                           => '/(cines via parque)/i',
            // 'Streaming'                        => '/(globoplay|netflix|primevideo|appletv|disney|hbomax|spotify|telecine)/i',
            // 'Clubes de Pontos / Milhas'        => '/(smiles|livelo|latampass|tudoazul)/i',
            // // 'primebr' e 'amazonprime' antes de 'Amazon' para não cair na regra genérica
            // 'Serviços e Assinaturas'           => '/(google\s*one|tagitau|sams|fing|meli|primebr|amazonprime|produtos globo)/i',
            // 'Passagens Aéreas (GOL/LATAM)'     => '/(gol|latam|voegol|azul\s*linhas)/i',
            // 'Viagens, Turismo e Hospedagem'    => '/(booking|loumar|hotel|pousada|resort|decolar)/i',

            // // SAÚDE E BEM-ESTAR
            // 'Planos de academias'              => '/(totalpass|wellhub|smartfit|bodytech)/i',
            'Farmácia'                         => '/(drogaria|drogas|venancio|pacheco|raia$|drogasmil|farmalife|farmacia)/i',

            // // COMPRAS E VAREJO
            'Amazon'                           => '/(amazon)/i',
            'Mercado Livre'                    => '/(mercadolivre)/i',
            'Shopee'                           => '/(shopee)/i',
            'Shein'                            => '/(shein)/i',
            // 'Compras e Varejo'                 => '/(aliexpress|magalu|americanas|lojas americ|amigao|lilipe)/i',

            // // ALIMENTAÇÃO
            // 'Supermercado'                     => '/(supermercad|vianense|guanabara|guanab|atacadao|prezunic|assai|megabox|casafruti|horti|hortifruti|casas pedro|supermarket|sendas)/i',
            // 'Delivery'                         => '/(ifd\*|ifood|99food|ze\s*delivery)/i',
            // 'Fast Food'                        => '/(mc\s*donald|mcdonald|burger|bobs|subway)/i',
            // 'Padaria'                          => '/(panificadora|padaria)/i',
            // 'Restaurantes'                     => '/(mocellin|rio\s*brasa|outback|restaurante|churrascaria|oliva|gallo da vil|parme|bauru gourme|pizzaria|madero)/i',
            // 'Lanches'                          => '/(hortela|kopenhagen|cacau|alzira|salgados)/i',

            // // MORADIA
            // 'Condomínio'                       => '/(grupamento resi|condominio|condomi|residencial)/i',
            // 'Energia Elétrica'                 => '/light/i',
            // 'Gás'                              => '/(companhia\s+distribui|ceg)/i',

            // // VEÍCULOS
            // 'Combustível'                      => '/(posto|petrobras|ipiranga|shell)/i',
            // 'Seguro Auto'                      => '/(porto\s*seguro|bradesco\s*seguros|azul\s*seguros|tokio\s*marine|sulamerica)/i',
            // 'Manutenção e Peças'               => '/(toyota|hyundai|oficina|autocenter)/i',
            // 'Manutenção da casa'               => '/(fluzao)/i',
            // 'IPVA / Licenciamento'             => '/(gringo|detran|ipva)/i',

            // // TRANSPORTE DIÁRIO
            // 'Uber'                             => '/(uber|uber_credit|99app)/i',
            // '99'                               => '/(99app|99\s*ride)/i',

            // // CUIDADOS PESSOAIS E VESTUÁRIO
            // 'Roupas e Calçados'                => '/(south|riachuelo|daluz|adidas|nike|cea|lojasriachue|botanope|calcados son|centauro|moda|havaianas)/i',

            // // DESPESAS FINANCEIRAS (faturas — fallback para o que não bateu acima)
            // 'Taxas Bancárias'                  => '/(iof|anuidade diferenci|encargos)/i',
            // 'Juros e Multas'                   => '/(juros pagamento contas|atraso|atrasada|valor pendente)/i',
        ];

        $descricao = mb_strtolower($descricao, 'UTF-8');

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