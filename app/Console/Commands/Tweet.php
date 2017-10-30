<?php

namespace App\Console\Commands;

use App\Bank;
use App\Tweet as Model;
use Illuminate\Console\Command;
use Thujohn\Twitter\Facades\Twitter;
use Waavi\UrlShortener\UrlShortener;

class Tweet extends Command
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
     * @var Bank
     */
    private $model;

    /**
     * @var UrlShortener
     */
    private $shortener;

    /**
     * @var
     */
    private $banks;

    /**
     * @var
     */
    private $bank;

    /**
     * @var
     */
    private $towns;

    /**
     * Create a new command instance.
     *
     * @param Bank         $model
     * @param UrlShortener $shortener
     */
    public function __construct(Bank $model, UrlShortener $shortener)
    {
        parent::__construct();
        $this->model = $model;
        $this->shortener = $shortener;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Twitter::postTweet(['status' => $this->getStatus()]);
        Model::create(['status' => $this->getStatus()]);
    }

    /**
     * @return string
     */
    private function getStatus()
    {
        $this->setBank();

        return trim(implode(' ', [
            $this->getBankName(),
            'urgently need',
            $this->getIRandomItem(),
            '#' . $this->bank->town,
            '#foodbanks',
            $this->shortener->driver('google')->shorten($this->bank->url),
        ]));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    private function setBank()
    {
        $this->banks = Bank::whereNotNull('products')->where('town', '!=', '')->get();

        $this->setTowns();

        $this->bank = $this->banks->filter(function ($b) {
            return $this->towns->contains($b->town);
        })->random();
    }

    /**
     * Set the towns with multiple foodbanks
     *
     * @return mixed
     */
    private function setTowns()
    {
        $this->towns = $this->banks
            ->groupBy('town')
            ->filter(function ($t) {
                return $t->count() > 1;
            })
            ->map(function ($town, $key) {
                return $key;
            })
            ->values();
    }

    /**
     * @return string
     */
    private function getIRandomItem()
    {
        return strtolower(collect(json_decode($this->bank->products))->random());
    }

    /**
     * @return string
     */
    private function getBankName()
    {
        return $this->bank->twitter && $this->bank->twitter != 'TrussellTrust'
            ? '@' . $this->bank->twitter
            : $this->bank->name;
    }
}
