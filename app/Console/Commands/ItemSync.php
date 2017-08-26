<?php

namespace App\Console\Commands;

use App\Bank;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;

class ItemSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!Cache::has('nantwich')) {
            $client = new Client();
            $res = $client->get('https://nantwich.foodbank.org.uk/give-help/donate-food/');
            Cache::forever('nantwich', $res->getBody()->getContents());
        }

        $dom = new Crawler(Cache::get('nantwich'));

        $items = $dom->filterXPath('//ul[@class="page-section--sidebar__block-shopping-list"]')
                     ->first()
                     ->text();

        Bank::create([
            'name' => 'Nantwich',
            'products' => json_encode(array_map('trim', explode("\n", trim($items)))),
        ]);
    }
}
