<?php

namespace App\Console\Commands;

use App\Bank;
use App\Product;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\ConnectException;

class FetchItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all items the foodbanks need';

    /**
     * @var Client
     */
    protected $client;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Bank::all()->each(function ($bank) {
            try {
                $this->line($bank->name);
                $products = $this->createProducts($this->shoppingList($bank));
                $bank->products()->sync($products->pluck('id'));
            } catch (ConnectException $e) {
                $this->error("Could not find information for $bank->name");
            } catch (\Exception $e) {
                $this->error("Could not process items for $bank->name $bank->url");
            }
        });
    }

    /**
     * @param $items
     * @return \Illuminate\Support\Collection
     */
    private function createProducts($items)
    {
        return tap(collect(), function ($products) use ($items) {
            $items->each(function ($item) use ($products) {
                $item = str_replace(['*', '.'], '', $item);
                $products->push(Product::firstOrCreate(['name' => ucwords(trim($item))]));
            });
        });
    }

    /**
     * @param $bank
     * @return array
     */
    public function shoppingList($bank)
    {
        $dom = new Crawler($this->html($bank));

        $items = $dom->filterXPath('//ul[@class="page-section--sidebar__block-shopping-list"]')->first()->text();

        return $this->cleansed($items);
    }

    /**
     * @param $bank
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function html($bank)
    {
        return $this->client->get($bank->url)->getBody()->getContents();
    }

    /**
     * @param $items
     * @return array
     */
    public function cleansed($items)
    {
        return collect(array_map('trim', explode("\n", trim($items))));
    }
}
