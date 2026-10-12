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

            // Relacionamentos Opcionais
            $table->foreignUuid('meio_pagamento_id')->nullable()->constrained('meios_pagamentos', 'id')->nullOnDelete();
            $table->foreignUuid('fatura_id')->nullable()->constrained('faturas', 'id')->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('categoria_id')->nullable()->constrained('categorias', 'id')->onUpdate('cascade')->nullOnDelete();

            // Dados Principais
            $table->decimal('valor', 12, 2);
            $table->string('descricao');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            
            // Controle de Parcelamento
            $table->boolean('parcelado')->default(false);
            $table->integer('parcela')->default(1);
            $table->integer('qtd_parcelas')->default(1);
            
            $table->enum('tipo', ['receita','despesa','transferencia']);
            $table->enum('status', ['pendente','pago'])->default('pendente');
            $table->enum('origem', ['fatura','extrato'])->default('fatura');

            // Dados de moeda estrangeira (preenchidos apenas quando aplicável)
            $table->boolean('moeda_estrangeira')->default(false);
            $table->char('moeda', 3)->default('BRL');
            $table->decimal('valor_moeda', 12, 2)->nullable();
            $table->decimal('cotacao', 12, 2)->nullable();

            // Dados do cartão (preenchidos apenas para lançamentos de fatura)
            $table->string('tipo_cartao')->nullable();
            $table->string('numero_cartao')->nullable();
            $table->string('nome_titular')->nullable();

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
