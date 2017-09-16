<?php

namespace App\Console;

use App\Console\Commands\Tweet;
use App\Console\Commands\FindBanks;
use App\Console\Commands\FetchItems;
use App\Console\Commands\GeocodeBank;
use App\Console\Commands\SkillReminder;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FindSocialHandles;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        GeocodeBank::class,
        FindBanks::class,
        FindSocialHandles::class,
        FetchItems::class,
        SkillReminder::class,
        Tweet::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('banks:items')->daily();
        $schedule->command('tweet:items')->hourlyAt('7')->between('7:00', '22:00');
        $schedule->command('tweet:reminder')->weekly()->sundays()->at('19:23');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
