<?php

namespace App\Console\Commands;

use App\Bank;
use Illuminate\Console\Command;
use Thujohn\Twitter\Facades\Twitter;

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
        $towns = ['Manchester', 'London', 'Birmingham', 'Bristol', 'Edinburgh', 'Glasgow', 'Liverpool', 'Nottingham', 'Sheffield'];
        $bank = Bank::whereNotNull('products')->whereIn('town', $towns)->get()->random();
        $items = strtolower(collect(json_decode($bank->products))->random());
        $name = $bank->twitter && $bank->twitter != 'TrussellTrust' ? "@$bank->twitter": $bank->name;
        Twitter::postTweet(['status' => "$name urgently need $items #$bank->town"]);
    }
}
