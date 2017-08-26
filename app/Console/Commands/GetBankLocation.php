<?php

namespace App\Console\Commands;

use App\Bank;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class GetBankLocation extends Command
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
            $domain = explode('/', $bank->url);
            $this->info($bank->name . ', ' . $bank->url);

            try {
                $res = $this->client->get($bank->url);

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
