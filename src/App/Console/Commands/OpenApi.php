<?php

namespace Voice\OpenApi\App\Console\Commands;

use Illuminate\Console\Command;

class OpenApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asseco:open-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OpenApi yaml file from app routes.';

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
        return 0;
    }
}
