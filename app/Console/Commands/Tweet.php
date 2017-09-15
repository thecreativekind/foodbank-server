<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Bank;

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
        $items = collect(json_decode($bank->products))->random();
        \Twitter::postTweet(['status' => "$bank->name urgently needs $items #$bank->town"]);
    }
}
