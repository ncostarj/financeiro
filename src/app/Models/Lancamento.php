<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lancamento extends Model
{
    use HasUuids;

    protected $table = 'lancamentos';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'meio_pagamento_id',
        'fatura_id',
        'categoria_id',
        'descricao',
        'tipo',
        'status',
        'origem',
        'data_vencimento',
        'data_pagamento',
        'valor',
        'parcelado',
        'parcela',
        'qtd_parcelas',
        'moeda_estrangeira',
        'moeda',
        'valor_moeda',
        'cotacao',
        'tipo_cartao',
        'numero_cartao',
        'nome_titular',
    ];

    protected $casts = [
        'data_vencimento'   => 'date',
        'data_pagamento'    => 'date',
        'valor'             => 'decimal:2',
        'parcelado'         => 'boolean',
        'parcela'           => 'integer',
        'qtd_parcelas'      => 'integer',
        'moeda_estrangeira' => 'boolean',
        'valor_moeda'       => 'decimal:2',
        'cotacao'           => 'decimal:4',
    ];

    public function meioPagamento(): BelongsTo
    {
        return $this->belongsTo(MeioPagamento::class, 'meio_pagamento_id');
    }

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(Fatura::class, 'fatura_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
