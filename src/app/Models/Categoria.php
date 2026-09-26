<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasUuids;

    protected $table = 'categorias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'categoria_pai_id',
        'nome',
        'tipo',
        'cor'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_pai_id');
    }
    
    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'categoria_pai_id');
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'categoria_id');
    }

    public function limitesGastos(): HasMany
    {
        return $this->hasMany(LimiteGasto::class, 'categoria_id');
    }
}
