<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fatura_id')->constrained('faturas', 'id')->onUpdate('cascade')->onDelete('cascade');
            $table->date('data');
            $table->string('descricao');
            $table->boolean('parcelado');
            $table->integer('parcela');
            $table->integer('qtd_parcelas');
            $table->boolean('moeda_estrangeira');
            $table->char('moeda', 3);
            $table->decimal('valor_moeda', 8, 2);
            $table->decimal('cotacao', 8, 2);
            $table->decimal('valor', 8, 2);
            $table->string('tipo_cartao');
            $table->string('numero_cartao');
            $table->string('nome_titular');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};
