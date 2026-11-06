<?php

namespace App\Domain\Faturas\Actions;

use App\Domain\Financeiro\Importers\FaturaCsvImport;
use App\Domain\Financeiro\Importers\FaturaXlsImport;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Excel;

class LerFaturaAction {

    public function __construct(
        private readonly Excel $excel,
    ){}

    /** @return Collection<int, LancamentoDTO> */
    public function execute(string $caminho, string $tipo, string $delimitador = null): Collection {
    
        $importador = $this->resolverImportador($tipo, $delimitador);
        $this->excel->import($importador, $caminho);
        return $importador->getDadosNormalizados();

    }

    private function resolverImportador(string $tipo, string $delimiter) {
        return match($tipo) {
            'xls', 'xlsx' => new FaturaXlsImport(),
            'csv'         => new FaturaCsvImport($delimitador??';'),
            default       => throw  new InvalidArgumentException("Tipo de fatura não suportado: {$tipo}"),
        }
    }
}
