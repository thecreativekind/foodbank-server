<?php

namespace App\Console\Commands;

use App\Bank;
use App\Tweet;
use Illuminate\Console\Command;
use Thujohn\Twitter\Facades\Twitter;
use Waavi\UrlShortener\UrlShortener;

class PostTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send tweet about items from a random bank.';

    /**
     * @var UrlShortener
     */
    private $shortener;

    /**
     * Create a new command instance.
     *
     * @param UrlShortener $shortener
     */
    public function __construct(UrlShortener $shortener)
    {
        parent::__construct();
        $this->shortener = $shortener;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $status = $this->getStatus();
        Twitter::postTweet(['status' => $status]);
        Tweet::create(['status' => $status]);
    }

    /**
     * @return string
     */
    private function getStatus()
    {
        $bank = Bank::whereHas('products')->where('town', '!=', '')->get()->random();

        return trim(implode(' ', [
            $this->identifierFor($bank),
            'urgently need',
            $bank->products->pluck('name')->random(5)->implode(', '),
            '#' . str_replace(' ', '', ucwords($bank->town)),
            '#foodbanks',
            $this->shortener->driver('google')->shorten($bank->url),
        ]));
    }

    /**
     * @param $bank
     * @return string
     */
    private function identifierFor($bank)
    {
        return $bank->twitter && $bank->twitter != 'TrussellTrust'
            ? '@' . $bank->twitter
            : $bank->name;
    }
}
