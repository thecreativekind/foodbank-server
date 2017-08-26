<?php

namespace App\Console\Commands;

use App\Bank;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class SyncBankItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:banks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
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
            $this->info($bank->name . ', ' . 'https://' . $bank->slug . '.foodbank.org.uk/give-help/donate-food/');

            try {
                $res = $this->client->get('https://' . $bank->slug . '.foodbank.org.uk/give-help/donate-food/');

                $dom = new Crawler($res->getBody()->getContents());

                $items = $dom->filterXPath('//ul[@class="page-section--sidebar__block-shopping-list"]')
                             ->first()
                             ->text();

                $bank->update(['products' => json_encode(array_map('trim', explode("\n", trim($items))))]);
            } catch (\Exception $e) {
                //
            }
        });
    }
}
