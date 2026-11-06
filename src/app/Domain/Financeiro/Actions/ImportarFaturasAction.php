<?php

namespace App\Domain\Faturas\Actions;

use App\Domain\Financeiro\Actions\CategorizarLancamentoAction;
use App\Domain\Financeiro\Actions\RegistrarLancamentosAction;
use App\Domain\Financeiro\DTOs\LancamentoDTO;
use Illuminate\Support\Collection;

class ImportarFaturasAction
{

    public function __construct(
        private readonly LerFaturaAction             $ler,
        private readonly CategorizarLancamentoAction $categorizar,
        private readonly RegistrarLancamentosAction   $registrar,
    ) {}

    /** @return Collection<int, LancamentoDTO> */
    public function execute(string $caminho, string $tipo): Collection
    {

        $lancamentos = $this->ler->execute($caminho, $tipo)
            ->map(fn(LancamentoDTO $dto) => $this->categorizar->execute($dto));

        $this->registrar->execute($lancamentos);

        return collect();
    }
}
