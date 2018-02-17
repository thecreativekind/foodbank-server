<?php

namespace App\Console\Commands;

use App\Bank;
use Illuminate\Console\Command;
use Thujohn\Twitter\Facades\Twitter;

class TwitterFollow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:follow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto follows all food banks';

    /**
     * Create a new command instance.
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
        Bank::whereNotNull('twitter')->get()->unique('twitter')->each(function ($b) {
            try {
                Twitter::postFollow(['screen_name' => $b->twitter]);
            } catch (\Exception $e) {
                $this->error('Could not follow ' . $b->name);
            }
        });
    }
}
