<?php

namespace App\Exports\Sheets;

use App\Models\Categoria;
use App\Models\Fatura;
use App\Models\Lancamento;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * B1 = filtro de titular (dropdown), B2 = filtro de mês da fatura (dropdown).
 * A partir da linha 6, uma linha por categoria com o total (SUMIFS lendo a
 * aba Lancamentos já filtrado por titular + mês) e o percentual sobre o
 * total do recorte selecionado. O gráfico de pizza aponta para essa tabela,
 * então ele se atualiza sozinho quando os dropdowns mudam no Excel.
 *
 * IMPORTANTE: um arquivo .xls (binário antigo) não suporta esse tipo de
 * gráfico/validação do jeito como o PhpSpreadsheet gera — exporte como
 * .xlsx.
 */
class DashboardSheet implements FromArray, WithTitle, WithEvents
{
    private const LINHA_FILTRO_TITULAR = 1;
    private const LINHA_FILTRO_MES = 2;
    private const LINHA_CABECALHO_TABELA = 5;

    private array $titulares;
    private array $meses;
    private array $categorias;

    public function __construct()
    {
        // TODO: se a lista de titulares/meses ficar grande, considere paginar
        // ou revisar — aqui ela é montada a partir dos dados reais no banco.
        $this->titulares = Lancamento::query()
            ->select('nome_titular')
            ->distinct()
            ->pluck('nome_titular')
            ->map(fn ($t) => $t === null || $t === '' ? 'Sem Titular' : $t)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $this->meses = Fatura::query()
            ->orderBy('data_vencimento')
            ->pluck('data_vencimento')
            ->map(fn ($data) => Carbon::parse($data)->format('m/Y'))
            ->unique()
            ->values()
            ->all();

        $this->categorias = Categoria::query()->orderBy('nome')->pluck('nome')->all();
        $this->categorias[] = 'Sem Categoria';
    }

    public function array(): array
    {
        // Layout inicial (rótulos + valor padrão dos filtros). As fórmulas e o
        // dropdown em si são montados no AfterSheet — ver registerEvents().
        return [
            ['Titular', 'Todos'],
            ['Mês da Fatura', $this->meses[array_key_last($this->meses)] ?? ''],
            [],
            [],
            ['Categoria', 'Total (R$)', '% do total filtrado'],
        ];
    }

    public function title(): string
    {
        return 'Dashboard';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->configurarFiltros($sheet);
                $this->configurarTabelaCategorias($sheet);
                $this->configurarGrafico($sheet);
            },
        ];
    }

    private function configurarFiltros(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A5:C5')->getFont()->setBold(true);

        $celulaTitular = 'B' . self::LINHA_FILTRO_TITULAR;
        $validacaoTitular = $sheet->getCell($celulaTitular)->getDataValidation();
        $validacaoTitular->setType(DataValidation::TYPE_LIST);
        $validacaoTitular->setErrorStyle(DataValidation::STYLE_STOP);
        $validacaoTitular->setShowDropDown(true);
        $validacaoTitular->setFormula1('"Todos,' . implode(',', $this->titulares) . '"');

        $celulaMes = 'B' . self::LINHA_FILTRO_MES;
        $validacaoMes = $sheet->getCell($celulaMes)->getDataValidation();
        $validacaoMes->setType(DataValidation::TYPE_LIST);
        $validacaoMes->setErrorStyle(DataValidation::STYLE_STOP);
        $validacaoMes->setShowDropDown(true);
        $validacaoMes->setFormula1('"' . implode(',', $this->meses) . '"');
    }

    private function configurarTabelaCategorias(Worksheet $sheet): void
    {
        $primeiraLinha = self::LINHA_CABECALHO_TABELA + 1;
        $ultimaLinha = self::LINHA_CABECALHO_TABELA + count($this->categorias);
        $celulaTitular = '$B$' . self::LINHA_FILTRO_TITULAR;
        $celulaMes = '$B$' . self::LINHA_FILTRO_MES;

        $linha = $primeiraLinha;

        foreach ($this->categorias as $categoria) {
            $sheet->setCellValue("A{$linha}", $categoria);

            // Colunas da aba Lancamentos: C = Valor, D = Categoria, E = Titular, F = Mês da Fatura.
            // "Todos" vira o curinga "*"; senão usa o texto exato escolhido no dropdown.
            $sheet->setCellValue(
                "B{$linha}",
                "=SUMIFS(Lancamentos!C:C,Lancamentos!D:D,A{$linha},Lancamentos!F:F,{$celulaMes}," .
                "Lancamentos!E:E,IF({$celulaTitular}=\"Todos\",\"*\",{$celulaTitular}))"
            );

            $sheet->setCellValue(
                "C{$linha}",
                "=IFERROR(B{$linha}/SUM(\$B\${$primeiraLinha}:\$B\${$ultimaLinha}),0)"
            );
            $sheet->getStyle("C{$linha}")->getNumberFormat()->setFormatCode('0.0%');

            $linha++;
        }
    }

    private function configurarGrafico(Worksheet $sheet): void
    {
        $primeiraLinha = self::LINHA_CABECALHO_TABELA + 1;
        $ultimaLinha = self::LINHA_CABECALHO_TABELA + count($this->categorias);
        $qtdCategorias = count($this->categorias);

        $rotulos = [new DataSeriesValues('String', "Dashboard!\$A\${$primeiraLinha}:\$A\${$ultimaLinha}", null, $qtdCategorias)];
        $valores = [new DataSeriesValues('Number', "Dashboard!\$C\${$primeiraLinha}:\$C\${$ultimaLinha}", null, $qtdCategorias)];

        $serie = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            range(0, $qtdCategorias - 1),
            [],
            $rotulos,
            $valores
        );

        $plotArea = new PlotArea(null, [$serie]);
        $legenda = new Legend(Legend::POSITION_RIGHT, null, false);
        $titulo = new Title('Gastos por Categoria (% do recorte filtrado)');

        $grafico = new Chart('grafico_categorias', $titulo, $legenda, $plotArea);
        $grafico->setTopLeftPosition('E1');
        $grafico->setBottomRightPosition('N20');

        $sheet->addChart($grafico);
    }
}