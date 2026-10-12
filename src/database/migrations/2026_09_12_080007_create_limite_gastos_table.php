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
        Schema::create('limites_gastos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relacionamento com a categoria
            $table->foreignUuid('categoria_id')->constrained('categorias', 'id')->cascadeOnDelete();

            // Período de aplicação do teto de gastos 
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('ano');
            $table->decimal('limite_mensal', 12, 2);

            $table->timestamps();

            // Impede a criação de dois limites para a mesma categoria no mesmo mês/ano
            $table->unique(['categoria_id','mes','ano']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limites_gastos');
    }
};
