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
        Schema::create('faturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meio_pagamento_id')->constrained('meios_pagamentos', 'id')->onDelete('cascade');
            $table->date('data_fechamento');
            $table->date('data_vencimento');
            $table->decimal('total', 12, 2)->default(0.00);
            $table->json('resumo')->nullable();
            $table->enum('status', ['aberta', 'fechada','pago'])->default('aberta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};
