<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use TCG\Voyager\VoyagerServiceProvider;

class install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printer:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Laravel-Print';

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
        $this->call('key:generate');
        $this->info('Gracias por instalar Laravel-Print');
    }
}