<?php

namespace App\Domain\Financeiro\DTOs;

final class LancamentoDTO {
    public function __construct(
        public readonly string $data,
        public readonly string $descricao,
        public readonly float $valor,
        public readonly string $origem,
        public readonly ?string $categoria,
        public readonly array $meta = []
    ) {}

    public function comCategoria(string $categoria) {
        return new self(
            $this->data,
            $this->descricao,
            $this->valor,
            $this->origem,
            $categoria,
            $this->meta
        );
    }
}
