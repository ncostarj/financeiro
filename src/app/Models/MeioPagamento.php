<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeioPagamento extends Model
{
    use HasUuids;

    protected $table = 'meios_pagamentos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'tipo',
        'saldo_inicial',
        'limite_credito',
        'dia_fechamento',
        'dia_vencimento',
        'cor',
        'ativo'
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'limite_credito' => 'decimal:2',
        'dia_fechamento' => 'integer',
        'dia_vencimento' => 'integer',
        'ativo' => 'boolean',
    ];

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class);
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }
}
