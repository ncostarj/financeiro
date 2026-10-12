
# meios_pagamento
```php
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
```

# faturas

```php
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
            $table->enum('status', ['aberta', 'fechada','paga'])->default('aberta');

            $table->json('resumo');
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
```

# categorias

```php
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
            $table->foreignUuid('parent_id')->nullable()->constrained('categorias')->nullOnDelete();
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
```

# lancamentos

```php
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
            $table->string('descricao');
            $table->enum('tipo', ['receita','despesa','transferencia']);
            $table->enum('status', ['pendente','pago']);

            // Controle de Cliclos/Datas
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            
            // Valores e Moeda
            $table->decimal('valor', 12, 2);
            $table->boolean('moeda_estrangeira')->default(false);
            $table->char('moeda', 3)->default('BRL');
            $table->decimal('valor_moeda', 12, 2)->nullable();
            $table->decimal('cotacao', 12, 2)->nullable();

            // Controle de Parcelamento
            $table->boolean('parcelado')->default(false);
            $table->integer('parcela')->default(1);
            $table->integer('qtd_parcelas')->default(1);

            // Controle para lançamentos de crédito
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
```

# limite gastos

```php
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
```