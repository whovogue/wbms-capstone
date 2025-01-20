<?php

namespace App\Console;

use App\Console\Commands\CustomQueueWork;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        CustomQueueWork::class, // Register your custom command
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:queue-work')
            ->everySecond()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
