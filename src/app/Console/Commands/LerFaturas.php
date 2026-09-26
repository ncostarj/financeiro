<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LerFaturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faturas:ler-arquivo {caminho}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command para ler o arquivo xls da fatura informando o caminho do arquivo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
