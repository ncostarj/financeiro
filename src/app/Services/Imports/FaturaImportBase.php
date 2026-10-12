<?php

namespace App\Services\Imports;

use Illuminate\Support\Collection;

abstract class FaturaImportBase
{
    // Armazenará os dados já normalizados nesta instância
    protected Collection $dadosNormalizados;

    public function __construct()
    {
        $this->dadosNormalizados = collect();
    }

    /**
     * Retorna os dados que foram processados e normalizados por esta classe
     */
    public function getDadosNormalizados(): Collection
    {
        return $this->dadosNormalizados;
    }
}
