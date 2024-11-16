<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Schedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New Command to run Schedule command after minute to get News';

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
     * @return int
     */
    public function handle()
    {
        $this->info('Running scheduled tasks...');
        while (true) {
            $schedule = new Process(['php', 'artisan', 'schedule:run']);
            $schedule->run();
            sleep(60);
        }
    }
}
