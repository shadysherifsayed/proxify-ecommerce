<?php

namespace App\Console\Commands;

use App\Actions\SyncProductsAction;
use App\Models\Product;
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
        $startTime = microtime(true);

        $this->info('Starting product sync...');

        $syncProducts->execute();

        $endTime = microtime(true);

        $this->info('Product sync completed in '.round($endTime - $startTime, 2).' seconds.');

        $this->info(sprintf('%s products have been synced.', Product::count()));
    }
}
