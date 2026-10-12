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
        Schema::create('meios_pagamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->enum('tipo', ['conta_corrente', 'cartao_credito', 'dinheiro', 'investimento', 'beneficio']); // VR, VA, etc. 
            $table->decimal('saldo_inicial', 12, 2)->default(0.00);

            // Regras Exclusivas para cartão de crédito
            $table->decimal('limite_credito', 12, 2)->nullable();
            $table->unsignedTinyInteger('dia_fechamento')->nullable(); // Ex: dia 05
            $table->unsignedTinyInteger('dia_vencimento')->nullable(); // Ex: dia 1              

            $table->string('cor', 7)->nullable(); // Código Hex para tags no Vue (ex: '#8B5CF6')
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meios_pagamentos');
    }
};
