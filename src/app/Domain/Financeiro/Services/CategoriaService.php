<?php

namespace App\Domain\Financeiro\Services;

use App\Domain\Financeiro\Models\Categoria;

class CategoriaService
{
    public function identificarCategoria(string $descricao): ?Categoria
    {
        $descricao = strtolower($descricao);

        $regras = [
            // Categoria  => Regex

            'Taxas Bancárias' => '/(encargos|iof|anuidade)/i',
            'Luz' => '/(light)/i',
            'Gás' => '/(companhia\sdistribui)/i',
            'Condomínio' => '/(grupamento\sresi)/',
            'Supermercado' => '/(supermercados|guanabara|prezunic|megabox|hortifruti|casas\spedro)/i',
            'Cinema' => '/(cines)/i',
            'Restaurantes' => '/(oliva|outback|maderoindust|gallo\sda\svil|bauru\sgourme|pizzaria)/i',
            'Lanches' => '/(mania\sde\spip|rei\sdos\ssalgados|hortela)/i',
            'Padaria' => '/(padaria|forno\sdo\sdao)/i',
            'Doceria' => '/(kopenhagen|cacau\sshow|alzira)/i',
            'Farmácia' => '/(drogaria|venancio|drogasmil)/i',
            'Combustível' => '/(posto)/i',
            'Outras Compras' => '/(lilipe|amigao)/i',
            'Roupas e Calçados' => '/(cea|botanope)/i',
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
