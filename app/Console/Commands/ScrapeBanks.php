<?php

namespace App\Console\Commands;

use App\Bank;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeBanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:scrape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all Trussel foodbanks';

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
        $crawler = new Crawler(Storage::get('banks.html'));
        $crawler->filterXPath('//li')->each(function ($b) {
            $dom = new Crawler($b->html());
            $url = $dom->filterXPath('//a//@href')->first()->text();
            Bank::firstOrCreate([
                'name' => ucwords($dom->filter('h4')->first()->text()),
                'slug' => trim($domain = explode('/', $url)[5]),
                'address' => trim($dom->filterXPath('//div[@class="location__address"]')->first()->text()),
                'url' => $url,
            ]);
        });
    }
}
