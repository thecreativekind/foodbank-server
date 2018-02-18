<?php

namespace App\Console\Commands;

use App\Bank;
use App\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MostNeedItemTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:most-needed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $normalisedItems;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->normalisedItems = collect();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->buildNormalisedItems();
        $this->normalisedItems->groupBy('name')->sort()->map->first()->reverse()->take(25)->each(function ($item) {
            $this->line($item);
        });
    }

    /**
     * @return mixed
     */
    private function items()
    {
        $items = DB::table('bank_product')->join('products', 'bank_product.product_id', '=', 'products.id')->select('products.name')->get();

        return $items->map(function ($item) {
            return ['name' => trim(preg_replace('/\(.*\)/', '', $item->name))];
        });
    }

    /**
     * @return $this
     */
    private function buildNormalisedItems()
    {
        $this->items()->each(function ($item) {
            $match = $this->normalisedItems->first(function ($normed) use ($item) {
                $itemName = strtolower(str_replace([' ', '-'], '', $item['name']));
                $normedName = strtolower(str_replace([' ', '-'], '', $normed['name']));

                return str_contains($itemName, $normedName) || str_contains($normedName, $itemName);
            });

            if ($match) {
                return $this->normalisedItems->push($match);
            }

            return $this->normalisedItems->push($item);
        });
    }
}
