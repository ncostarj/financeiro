<?php

namespace Database\Seeders;

use App\Models\MeioPagamento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MeioPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $meiosPagamento = [
            [
                'nome' => 'Conta Corrente Itaú Uniclass',
                'tipo' => 'conta_corrente',
                'saldo_inicial' => 0,
                'limite_credito' => 37400.00,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#EC7000',
                'ativo' => true,
            ],
            [
                'nome' => 'Conta Corrente Itaú (Carolina)',
                'tipo' => 'conta_corrente',
                'saldo_inicial' => 0,
                'limite_credito' => null,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#EC7000',
                'ativo' => true,
            ],            
            [
                'nome' => 'Conta Corrente Bradesco Prime',
                'tipo' => 'conta_corrente',
                'saldo_inicial' => 0,
                'limite_credito' => 9000,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#ec1f00',
                'ativo' => true,
            ],
            [
                'nome' => 'Itaucard Visa Infinite',
                'tipo' => 'cartao_credito',
                'saldo_inicial' => 0.00,
                'limite_credito' => 36386.13,
                'dia_fechamento' => 2,
                'dia_vencimento' => 9,
                'cor' => '#000000',
                'ativo' => true,
            ],
            [
                'nome' => 'Nubank Platinum',
                'tipo' => 'cartao_credito',
                'saldo_inicial' => 0.00,
                'limite_credito' => 9400.00,
                'dia_fechamento' => 5,
                'dia_vencimento' => 12,
                'cor' => '#820AD1',
                'ativo' => true,
            ],
            [
                'nome' => 'Cajú Benefícios - Refeição',
                'tipo' => 'beneficio',
                'saldo_inicial' => 0,
                'limite_credito' => null,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#dd2626',
                'ativo' => true,
            ],
            [
                'nome' => 'Cajú Benefícios - Alimentação',
                'tipo' => 'beneficio',
                'saldo_inicial' => 0,
                'limite_credito' => null,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#ca5e5e',
                'ativo' => true,
            ],
            [
                'nome' => 'Cajú Benefícios - Mobilidade',
                'tipo' => 'beneficio',
                'saldo_inicial' => 0,
                'limite_credito' => null,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#6a1e1e',
                'ativo' => true,
            ],
            [
                'nome' => 'Dinheiro / Carteira',
                'tipo' => 'dinheiro',
                'saldo_inicial' => 0,
                'limite_credito' => null,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#10B981',
                'ativo' => true,
            ],            
            [
                'nome' => 'Reserva de Emergência',
                'tipo' => 'investimento',
                'saldo_inicial' => 0,
                'limite_credito' => null,
                'dia_fechamento' => null,
                'dia_vencimento' => null,
                'cor' => '#3B82F6',
                'ativo' => true,
            ],
        ];

        foreach ($meiosPagamento as $meio) {
            MeioPagamento::updateOrCreate(
                ['nome' => $meio['nome']],
                $meio
            );
        }
    }
}
