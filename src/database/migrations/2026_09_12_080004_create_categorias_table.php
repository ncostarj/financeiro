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
        Schema::create('categorias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Permite criar árvore de subcategorias
            $table->foreignUuid('categoria_pai_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nome');
            $table->enum('tipo', ['receita','despesa']);
            $table->string('cor', 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
