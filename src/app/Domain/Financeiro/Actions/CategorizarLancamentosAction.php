<?php

namespace App\Domain\Financeiro\Actions;

use App\Domain\Financeiro\Services\CategoriaService;
use App\Domain\Financeiro\DTOs\LancamentoDTO;

class CategorizarLancamentoAction {

    public function __construct(
        private readonly CategoriaService $categorias
    ) {}

    public function execute(LancamentoDTO $dto) : LancamentoDTO {
        $categoria = $this->categorias->identificarCategoria($dto->descricao);
        
        return $categoria
            ? $dto->comCategoria($categoria->nome)
            : $dto;
    }

}
