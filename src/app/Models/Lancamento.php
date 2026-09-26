<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lancamento extends Model
{
     use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lancamentos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;    

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descricao',
        'tipo',
        'status',
        'data_vencimento',
        'data_pagamento',
        'valor',
        'moeda_estrangeira',
        'moeda',
        'valor_moeda',
        'cotacao',
        'parcelado',
        'parcela',
        'qtd_parcelas',
        'valor',
        'tipo_cartao',
        'numero_cartao',
        'nome_titular',
    ];

protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor' => 'decimal:2',
        'moeda_estrangeira' => 'boolean',
        'valor_moeda' => 'decimal:2',
        'cotacao' => 'decimal:4',
        'parcelado' => 'boolean',
        'parcela' => 'integer',
        'qtd_parcelas' => 'integer',
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
