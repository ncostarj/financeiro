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
        'data',
        'descricao',
        'parcelado',
        'parcela',
        'qtd_parcelas',
        'moeda_estrangeira',
        'moeda',
        'valor_moeda',
        'cotacao',
        'valor',
        'tipo_cartao',
        'numero_cartao',
        'nome_titular',
    ];

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(Fatura::class);
    }    
}
