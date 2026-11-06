<?php

namespace App\Domain\Financeiro\Actions;

use App\Domain\Financeiro\DTOs\LancamentoDTO;
use App\Domain\Financeiro\Models\Categoria;
use App\Domain\Financeiro\Models\Lancamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrarLancamentosAction
{

    /**
     * @param Collection<int, LancamentoDTO> $lancamentos
     * @return int quantidade de lançamentos novos
     */
    public function execute(Collection $lancamentos): int
    {

        if ($lancamentos->isEmpty()) {
            return 0;
        }

        $categorias = Categoria::pluck('id', 'nome');

        $linhas = $lancamentos->map(fn(LancamentoDTO $dto) => [
            'hash'         => $this->hash($dto),
            'data'         => $dto->data,
            'descricao'    => $dto->descricao,
            'valor'        => $dto->valor,
            'origem'       => $dto->origem,
            'categoria_id' => $dto->categoria ? $categorias->get($dto->categoria) : null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ])->all();

        return DB::transaction(function () use ($linhas) {
            $antes = Lancamento::count();

            Lancamento::insertOrIgnore($linhas);

            return Lancamento::count() - $antes;
        });
    }

    private function hash(LancamentoDTO $dto)
    {
        return hash('sha256', implode('|', [
            $dto->data,
            $dto->descricao,
            number_format($dto->valor, 2, '.', ''),
            $dto->origem,
        ]));
    }
}
