<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SkillReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweet a reminder about the Alexa Skill';

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
        \Twitter::postTweet(['status' => "Have an @amazonecho? You can now install the Help My Foodbank #alexa skill to find out what your local foodbank urgently need."]);
    }
}
