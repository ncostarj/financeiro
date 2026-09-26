<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fatura extends Model
{
    use HasUuids;

    protected $table = 'faturas';

    protected $fillable = [
        'data_fechamento',
        'data_vencimento',
        'status',
        'total',
        'resumo'
    ];

    protected $casts = [
        'data_fechamento' => 'date',
        'data_vencimento' => 'date',
        'total' => 'decimal:2',
        'resumo' => 'array',
    ];

    public function meioPagamento(): BelongsTo
    {
        return $this->belongsTo(MeioPagamento::class, 'meio_pagamento_id');
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'fatura_id');
    }
}
