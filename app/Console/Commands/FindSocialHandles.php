<?php

namespace App\Console\Commands;

use App\Bank;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\ConnectException;

class FindSocialHandles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:social';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var Client
     */
    private $client;

    /**
     * Create a new command instance.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
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
                list($twitter, $fb) = $this->getSocialAccounts($bank);
                $bank->twitter = $twitter;
                $bank->facebook = $fb;

                if ($bank->isDirty()) {
                    $this->info('New social handles found!');
                    $bank->save();
                }
            } catch (ConnectException $e) {
                $this->error("Could not find information for $bank->name");
            } catch (\Exception $e) {
                $this->error("Could not process items for $bank->name $bank->url");
            }
        });
    }

    /**
     * @param $bank
     * @return array
     */
    public function getSocialAccounts($bank)
    {
        $dom = new Crawler($this->html($bank));

        return [
            str_replace(['https://twitter.com/', '@', '.'], '', $dom->filterXPath("//a[contains(@href, 'twitter')]/@href")->first()->text()),
            str_replace('https://www.facebook.com/', '', $dom->filterXPath("//a[contains(@href, 'facebook')]/@href")->first()->text()),
        ];
    }

    /**
     * @param $bank
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function html($bank)
    {
        return $this->client->get($bank->url)->getBody()->getContents();
    }
}
