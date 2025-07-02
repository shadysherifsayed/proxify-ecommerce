<?php

namespace App\Console\Commands;

use App\Actions\SyncProducts as SyncProductsAction;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync products with the external API';

    /**
     * Execute the console command.
     */
    public function handle(SyncProductsAction $syncProducts): void
    {
        $this->info('Starting product sync...');

        $syncProducts->execute();

        $this->info('Product sync completed successfully.');
    }
}
